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
 */
class ShellHelper
{
    public static function exec($commands)
    {
        $line = implode('; ', $commands);
        return "$ $line \n" . shell_exec($line);
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
 */
class LogHelper
{
    static $log_file;

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

    protected static function log_var($name, $var)
    {
        static::log($name . ': ' . print_r($var, true));
    }

    protected static function log_error($message)
    {
        static::log('ERROR: ' . $message);
    }
}

/**
 * Class for auto-deploy code through git
 *
 * Example for using:
 *
 * ```php
 * // Prepare config
 * $config = [
 *    'log_file' => 'log.txt',
 *    'project_root' => '../..',
 *    'key' => 'zo9t9wcuwfi6wbqvu78z9biks3l39rue',
 * ];
 *
 * // Run App
 * GitHelper::init($config);
 * ```
 */
class GitHelper
{
    /**
     * @var array
     */
    static $config;

    public static function init($config)
    {
        echo '\optimistex\deploy\GitHelper::init';

        // Conf
        static::$config = $config;
        LogHelper::init(static::$config['log_file']);

        // Run
        LogHelper::log('SESSION START');
        static::git_check_access();

        LogHelper::log(ShellHelper::exec([
            'cd ' . static::$config['project_root'],
            'git branch',
            'git pull',
        ]));

        static::end();
    }

    public static function end()
    {
        LogHelper::log('SESSION END');
        exit;
    }

    public static function git_check_access()
    {
        $access = !empty($_GET['key']) && static::$config['key'] === $_GET['key'];
        if ($access) {
            LogHelper::log('ACCESS');
        } else {
            LogHelper::log('DENY << ://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            static::end();
        }
    }
}
