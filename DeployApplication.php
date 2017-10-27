<?php
/**
 * Created by PhpStorm.
 * User: optimistex
 * Date: 18.10.17
 * Time: 0:49
 */

namespace optimistex\deploy;

/**
 * Main class for deploying
 * Usage example:
 *
 * ```php
 * // Run default commands
 * (new DeployApplication('security_key'))->run();
 *
 * // Run custom commands
 * (new DeployApplication('security_key'))->run([
 *      'echo Hello!',   // equal: $ echo Hello!
 *      'php' => '-v'    // equal: $ php -v ()
 * ]);
 * // for running "php" used key cause just "php" not working!
 * ```
 *
 * @package optimistex\deploy
 */
class DeployApplication
{
    /** @var bool */
    public $breakOnExecError = true;

    /**
     * Private key for protection
     * @var string
     */
    private $securityKey;

    /**
     * Path to log file
     * @var string
     */
    private $logFileName;

    /** @var boolean */
    private $hasAccess;

    /** @var boolean */
    private $isFirstLogging = true;

    /** @var boolean */
    private $logError = false;

    /** @var bool */
    private $hasExecError = false;

    /**
     * DeployApplication constructor.
     * @param string $securityKey string key for protect application
     * @param string $workPath set working path for executing all commands
     * @param string $logFileName path to a log file
     */
    public function __construct($securityKey, $workPath = '.', $logFileName = 'git-deploy-log.txt')
    {
        $this->securityKey = $securityKey;
        $this->logFileName = getcwd() . '/' . $logFileName;
        chdir($workPath);
        putenv('HOME=' . getcwd());
    }

    /**
     * Fastest executing for typical cases
     * @param array $customCommands
     * @return bool
     */
    public function run(array $customCommands = [])
    {
        $this->begin();
        $res = $this->execute($customCommands);
        $this->end();
        return $res;
    }

    /**
     * Begin log file
     */
    public function begin()
    {
        if ($this->checkSecurity()) {
            $this->logDated('SESSION START');
        }
    }

    /**
     * Executing command like a command lines
     * @param array $customCommands you can execute custom commands
     * @return bool
     */
    public function execute(array $customCommands = [])
    {
        if (!$this->checkSecurity()) {
            return false;
        }
        $this->hasExecError = false;
        if (empty($customCommands)) {
            $this->exec([
                'git branch && git fetch',
                'git log @{u} ^HEAD --pretty=format:"%h - %an:  %s"',
                'git pull',
            ]);
        } else {
            $this->exec($customCommands);
        }
        return $this->hasExecError;
    }

    /**
     * Finish logging and output all content from the log file
     */
    public function end()
    {
        if ($this->checkSecurity()) {
            $this->logDated('SESSION END ' . ($this->hasExecError ? 'WITH ERROR' : 'SUCCESSFUL'));
        }

        if ($this->logError) {
            echo 'Write log failed';
        } else if (file_exists($this->logFileName)) {
            echo '<h1>LOG </h1><pre>';
            echo file_get_contents($this->logFileName);
            echo '</pre>';
        } else {
            echo 'A log file not found';
        }
    }

    /**
     * Returning an absolute path to "php". It is useful, cause just "php" not working!
     * @return string
     */
    public function php()
    {
        if (defined('PHP_BINDIR') && PHP_BINDIR) {
            return PHP_BINDIR . '/php';
        }
        if (defined('PHP_BINARY') && PHP_BINARY) {
            return PHP_BINARY . '/php';
        }
        if (defined('PHP_BINDER') && PHP_BINDER) {
            return PHP_BINDER . '/php';
        }
        return 'php';
    }

    private function checkSecurity()
    {
        if ($this->hasAccess === null) {
            $this->hasAccess = false;
            if (isset($_GET['key']) && !empty($_GET['key'])) {
                if ($this->securityKey === $_GET['key']) {
                    $this->logDated('ACCESS IS OBTAINED');
                    $this->hasAccess = true;
                } else {
                    $this->logDated(
                        'DENY << ://'
                        . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown-domain')
                        . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')
                    );
                }
            }
        }
        return $this->hasAccess;
    }

    private function exec(array $commands)
    {
        foreach ($commands as $key => $command) {
            $response = [];
            if (is_array($command)) {
                $this->exec($command);
            } else {
                if ($key === 'php') {
                    $command = $this->php() . ' ' . $command;
                } else if (is_string($key)) {
                    $this->extendEnvironmentPath($key);
                    $command = $key . ' ' . $command;
                }
                $this->logDated('$ ' . $command);
                exec($command . ' 2>&1', $response, $error_code);
                if ($error_code > 0 && empty($response)) {
                    $response = array('Error: ' . $error_code);
                    $this->hasExecError = true;
                }
                $response = implode("\n", $response);
                $this->log($response . "\n" . ($response ? "\n" : ''));
            }
            if ($this->hasExecError && $this->breakOnExecError) {
                return;
            }
        }
    }

    private function extendEnvironmentPath($command)
    {
        $extPath = '';
        if ($command === 'php') {
            $extPath = ':' . dirname($this->php());
        } else {
            exec('dirname $(whereis ' . $command . ') 2>&1', $response, $error_code);
            if (is_array($response)) {
                foreach (array_unique($response) as $path) {
                    if ($path !== '.') {
                        $extPath .= ':' . $path;
                    }
                }
            }
        }
        if (!empty($extPath)) {
            putenv('PATH=' . getenv('PATH') . $extPath);
        }
    }

    private function logDated($message)
    {
        if ($this->isFirstLogging) {
            $this->log("\n==============================\n");
            $this->isFirstLogging = false;
        }

        $this->log(date('Y.m.d H:i:s') . "\t" . $message . "\n");
    }

    private function log($message)
    {
        if (empty($this->logFileName)) {
            return;
        }
        if (file_put_contents($this->logFileName, $message, FILE_APPEND | LOCK_EX) === false) {
            $this->logError = true;
        } else {
            flush();
        }
    }
}
