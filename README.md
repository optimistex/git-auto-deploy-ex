Auto deploy project through GIT
===========

It does auto deploy your site to hosting

- [Requirements](#requirements)
- [Start to use through COMPOSER](#start-to-use-through-composer)
- [Start to use through NPM](#start-to-use-through-npm)
- [Extended deploy with custom commands](#extended-deploy-with-custom-commands)

# Requirements

1. PHP 5.4 or higher 
2. Access to perform **shell** commands
3. Installed **GIT** on target hosting

# Start to use through COMPOSER

The main using through composer.

1. Install package:
    ```php
    $ composer require optimistex/git-auto-deploy-ex
    ```
         
2. Make file ``deploy.php`` with content:
    ```php
    <?php
    require_once 'vendor/autoload.php';
    // Add secret code in the first parameter for protection
    (new \optimistex\deploy\DeployApplication('ytJHvMHFdTYUryDhmJkjFjFiYk'))->run();
    ```

3. Configure WebHook for send request to:

        http://your.domain/deploy.php?key=ytJHvMHFdTYUryDhmJkjFjFiYk
        
4. Visit page ``http://your.domain/deploy.php`` to check log history        
        
Do not forget to change the secret code ``ytJHvMHFdTYUryDhmJkjFjFiYk``
         
# Start to use through NPM
         
If you making SPA and the files is located on a php hosting, then you can does auto deployment through the package.
         
1. Install package:
    ```bash
    $ npm i git-auto-deploy-ex
    ```
         
2. Make file ``deploy.php`` with content:
    ```php
    <?php
    require_once '\path\to\DeployApplication';
    // Add secret code in the first parameter for protection
    (new \optimistex\deploy\DeployApplication('ytJHvMHFdTYUryDhmJkjFjFiYk'))->run();
    ```

3. Configure WebHook for send request to:

        http://your.domain/deploy.php?key=ytJHvMHFdTYUryDhmJkjFjFiYk
        
4. Visit page ``http://your.domain/deploy.php`` to check log history                 

# Extended deploy with custom commands

For extended deployment make the file ``deploy.php`` with code:

```php
<?php

use optimistex\deploy\DeployApplication;

require_once 'vendor/autoload.php';

(new DeployApplication('security_key'))->run([  // executing custom commands
    'git branch',                               // equal: $ git branch
    'git pull',                                 // equal: $ git pull
    'php' => 'composer.phar install',           // equal: $ php composer.phar install
    ['php' => 'yii migrate --interactive=0'],   // equal: $ php yii migrate --interactive=0
]);
```

The line ``'php' => 'composer.phar install'`` is used for expanding "php" to absolute path. 
An absolute path is required because "php" doesn't work using relative path!
