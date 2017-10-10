Auto deploy project through GIT
===========

#Requirements

1. PHP server
2. Access to perform **shell** commands

#Start to use

Change config in file "**bitbucket-webhook.php**":

    * **log_file** - "path/to/log/file". Here will be stored log of all operations.
    * **project_root** -  "path/to/project/root". It is root for executing git commands. 
    * **key** - password for protect your project. Use longest passwords for  different projects.

Send "**GET**" request to file bitbucket-webhook.php with "**key**" from your config. 
It is perform command ``"git pull"``. 

#Example to use

1. Suppose we have project "example" available from domain "example.com".
2. Put directory "auto-deploy" into "example".
3. Change config in file "auto-deploy/bitbucket-webhook.php":

        $config = [
            'log_file' => 'log.txt',
            'project_root' => '..',
            'key' => 'Q3jvGxzrE4vT5hJ2Uejc7dRBCxrx8FXHufjR', // Do not forget to generate secret-code!
        ];

4. Configure "Webhooks" of your git repository (like as bitbucket.org or github.com) for sending request:
  
        http://example.com/auto-deploy/bitbucket-webhook.php?key=Q3jvGxzrE4vT5hJ2Uejc7dRBCxrx8FXHufjR
        
5. Push new commit from your PC to repository.
6. Check log. Visit url ``http://example.com/auto-deploy/``