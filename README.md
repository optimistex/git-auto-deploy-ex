Auto deploy project through GIT
===========

It does auto deploy your site to hosting

# Requirements

1. PHP server
2. Access to perform **shell** commands
3. Installed **GIT** on target hosting

# Start to use

1. Install package:
    ```php
    $ composer require optimistex/git-auto-deploy-ex
    ```
         
2. Make file ``deploy.php`` with content:
    ```php
    <?php
    require_once 'vendor/autoload.php';
    // Add secret code in the first parameter for protection
    \optimistex\deploy\GitHelper::run('ytJHvMHFdTYUryDhmJkjFjFiYk');
    ```

3. Configure WebHook for send request to:

        http://your.domain/deploy.php?key=ytJHvMHFdTYUryDhmJkjFjFiYk
        
4. Visit page ``http://your.domain/deploy.php`` for check log history        
        
Do not forget to change the secret code ``ytJHvMHFdTYUryDhmJkjFjFiYk``
         
### Extended deploy with custom commands

For extended deployment make the file ``deploy.php`` with code:

```php
<?php

use optimistex\deploy\ShellHelper;
use optimistex\deploy\DeployApplication;

require_once 'vendor/autoload.php';

$app = new DeployApplication('security_key');
$app->begin();
$app->execute([ // executing custom commands
    'git branch',
    'git pull',
    ShellHelper::php() . ' composer.phar install', // install packages
]);
$app->end();
```

``ShellHelper::php()`` - equal ``php``, but used becouse just "php" not working! 