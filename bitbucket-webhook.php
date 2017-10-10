<?php
/**
 * Created by PhpStorm.
 * User: optimistex
 * Date: 21.05.16
 * Time: 23:32
 *
 * Example request url:
 * http://example.ru/auto-deploy/bitbucket-webhook.php?key=Q3jvGxzrE4vT5hJ2Uejc7dRBCxrx8FXHufjR
 */

// Use shell_lib
require_once 'shell_lib.php';

// Prepare config
$config = [
    'log_file' => 'log.txt',
    'project_root' => '..',
    'key' => 'Q3jvGxzrE4vT5hJ2Uejc7dRBCxrx8FXHufjR',
];

// Run App
GitHelper::init($config);
