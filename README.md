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

# Contribute

For running tests just run ``$ make``

# LICENSE
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are
    met:

    1   Redistributions of source code must retain the above copyright
        notice, this list of conditions and the following disclaimer.
    2.  Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.
    3.  The name of the author may not be used to endorse or promote
        products derived from this software without specific prior written
        permission.

    THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
    IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
    INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
    SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
    HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
    STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
    ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.