<?php
/**
 * Created by PhpStorm.
 * User: optimistex
 * Date: 02.06.16
 * Time: 17:09
 */

namespace optimistex\deploy;

/**
 * Class for executing shell commands
 *
 * Example for using:
 *
 * ```php
 * echo ShellHelper::exec([
 *    'cd ../..',
 *    'ls'
 * ]);
 * ```
 * @deprecated
 */
class ShellHelper
{
    public static function exec(array $commands)
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

    /**
     * Returning an absolute path to "php". It is useful, cause just "php" not working!
     * @return string
     * @deprecated
     */
    public static function php()
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
}

/**
 * Class for saving data to log-file
 *
 * Example for using:
 *
 * ```php
 * LogHelper::init('log_file.txt');
 * LogHelper::log('message for logging');
 * ```
 * @deprecated
 */
class LogHelper
{
    public static $log_file;

    public static function init($log_file)
    {
        static::$log_file = $log_file;
    }

    public static function log($message)
    {
        if (empty(static::$log_file)) {
            return;
        }

        static $is_first = true;
        if ($is_first) {
            file_put_contents(static::$log_file, "\n==============================\n", FILE_APPEND | LOCK_EX);
            $is_first = false;
        }

        $datetime = date('Y.m.d H:i:s');
        file_put_contents(static::$log_file, $datetime . "\t" . $message . "\n", FILE_APPEND | LOCK_EX);
        flush();
    }
}
