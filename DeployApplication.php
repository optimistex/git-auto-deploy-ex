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
 * (new DeployApplication('security_key'))->run();
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
    private $log_file;

    /** @var boolean */
    private $hasAccess;

    public function __construct($securityKey, $project_root = '.', $log_file = 'git-deploy-log.txt')
    {
        $this->securityKey = $securityKey;
        $this->log_file = getcwd() . '/' . $log_file;
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
            $this->log('SESSION START');
        }
    }

    public function execute(array $customCommands = [])
    {
        if (!$this->checkSecurity()) {
            return;
        }
        if (empty($customCommands)) {
            $this->log($this->exec([
                'git branch',
                'git pull',
            ]));
        } else {
            $this->log($this->exec($customCommands));
        }
    }

    public function end()
    {
        if ($this->checkSecurity()) {
            $this->log('SESSION END');
        }
        if (file_exists($this->log_file)) {
            echo '<h1>LOG </h1><pre>';
            echo file_get_contents($this->log_file);
            echo '</pre>';
        } else {
            echo 'log not found';
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
                    $this->log('ACCESS IS OBTAINED');
                    $this->hasAccess = true;
                } else {
                    $this->log(
                        'DENY << ://' . ($_SERVER['HTTP_HOST'] ?? 'unknown-domain') . ($_SERVER['REQUEST_URI'] ?? '')
                    );
                }
            }
        }
        return $this->hasAccess;
    }

    private function exec(array $commands)
    {
        $res = "Executing shell commands:\n";
        foreach ($commands as $command) {
            $response = [];
            exec($command . ' 2>&1', $response, $error_code);
            if ($error_code > 0 && empty($response)) {
                $response = array('Error: ' . $error_code);
            }
            $response = implode("\n", $response);
            $res .= "$ $command \n{$response}\n" . ($response ? "\n" : '');
        }
        return $res;
    }

    private function log($message)
    {
        if (empty($this->log_file)) {
            return;
        }

        static $is_first = true;
        if ($is_first) {
            file_put_contents($this->log_file, "\n==============================\n", FILE_APPEND | LOCK_EX);
            $is_first = false;
        }

        $datetime = date('Y.m.d H:i:s');
        file_put_contents($this->log_file, $datetime . "\t" . $message . "\n", FILE_APPEND | LOCK_EX);
        flush();
    }
}
