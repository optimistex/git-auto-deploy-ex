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
 * Example for use it:
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
    private $is_first = true;

    public function __construct($securityKey, $project_root = '.', $logFileName = 'git-deploy-log.txt')
    {
        $this->securityKey = $securityKey;
        $this->logFileName = getcwd() . '/' . $logFileName;
        chdir($project_root);
        putenv('HOME=' . getcwd());
    }

    /**
     * Fastest executing for typical cases
     * @param array $customCommands
     */
    public function run(array $customCommands = [])
    {
        $this->begin();
        $this->execute($customCommands);
        $this->end();
    }

    public function begin()
    {
        if ($this->checkSecurity()) {
            $this->logDated('SESSION START');
        }
    }

    public function execute(array $customCommands = [])
    {
        if (!$this->checkSecurity()) {
            return;
        }
        if (empty($customCommands)) {
            $this->exec(['git branch', 'git pull && git log -1']);
        } else {
            $this->exec($customCommands);
        }
    }

    public function end()
    {
        if ($this->checkSecurity()) {
            $this->logDated('SESSION END');
        }
        if (file_exists($this->logFileName)) {
            echo '<h1>LOG </h1><pre>';
            echo file_get_contents($this->logFileName);
            echo '</pre>';
        } else {
            echo 'log not found';
        }
    }

    /**
     * Returning an absolute path to "php". It is useful, cause just "php" not working!
     * @return string
     */
    public function php(): string
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

    private function checkSecurity(): bool
    {
        if ($this->hasAccess === null) {
            $this->hasAccess = false;
            if (isset($_GET['key']) && !empty($_GET['key'])) {
                if ($this->securityKey === $_GET['key']) {
                    $this->logDated('ACCESS IS OBTAINED');
                    $this->hasAccess = true;
                } else {
                    $this->logDated(
                        'DENY << ://' . ($_SERVER['HTTP_HOST'] ?? 'unknown-domain') . ($_SERVER['REQUEST_URI'] ?? '')
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
                }
                $this->logDated('$ ' . $command);
                exec($command . ' 2>&1', $response, $error_code);
                if ($error_code > 0 && empty($response)) {
                    $response = array('Error: ' . $error_code);
                }
                $response = implode("\n", $response);
                $this->log($response . "\n" . ($response ? "\n" : ''));
            }
        }
    }

    private function logDated(string $message)
    {
        if ($this->is_first) {
            $this->log("\n==============================\n");
            $this->is_first = false;
        }

        $this->log(date('Y.m.d H:i:s') . "\t" . $message . "\n");
    }

    private function log(string $message)
    {
        if (empty($this->logFileName)) {
            return;
        }

        file_put_contents($this->logFileName, $message, FILE_APPEND | LOCK_EX);
        flush();
    }
}
