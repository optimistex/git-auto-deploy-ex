<?php

use PHPUnit\Framework\TestCase;
use optimistex\deploy\DeployApplication;

class DeployApplicationTest extends TestCase
{
    private $fileName = 'git-deploy-log.txt';

    public function setUp()
    {
        parent::setUp();
        @unlink($this->fileName);
    }

    public function tearDown()
    {
        @unlink($this->fileName);
        parent::tearDown();
    }

    public function testKeyIsEmpty()
    {
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->run(['echo 111']);

        $this->assertFileNotExists($this->fileName);
        $app->begin();
        $app->execute(['echo 111']);
        $app->end();
        $this->assertFileNotExists($this->fileName);
    }

    public function testKeyInvalid()
    {
        $_GET['key'] = 'sdfg';
        $_SERVER['HTTP_HOST'] = 'test.domain';
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->execute(['echo testing_echo']);
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp('/====+.+DENY.+test.domain/s', $log);
    }

    public function testKeyValidDefault()
    {
        $_GET['key'] = '123';
        $_SERVER['HTTP_HOST'] = 'test.domain';
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->execute();
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp('/.+ACCESS IS OBTAINED.+' .
            date('Y.m.d H:i:.+') . '\$ git branch.+' .
            date('Y.m.d H:i:.+') . '\$ git pull/si',
            $log
        );
    }

    public function testKeyValidCustom()
    {
        $_GET['key'] = '123';
        $_SERVER['HTTP_HOST'] = 'test.domain';
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->execute([
            'echo testing_echo',
            $app->php() . ' -v'
        ]);
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp(
            '/.+ACCESS IS OBTAINED.+' .
            date('Y.m.d H:i:.+') . '\$ echo testing_echo.+' .
            'testing_echo.+' .
            date('Y.m.d H:i:.+') . '\$ .*php -v.+' .
            'php ' . PHP_VERSION . '.+/si',
            $log
        );
    }

    public function testKeyValidPhp()
    {
        $_GET['key'] = '123';
        $_SERVER['HTTP_HOST'] = 'test.domain';
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->execute([
            'echo testing_echo',
            'php' => '-v && echo 123412'
        ]);
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp(
            '/.+ACCESS IS OBTAINED.+' .
            date('Y.m.d H:i:.+') . '\$ echo testing_echo.+' .
            'testing_echo.+' .
            date('Y.m.d H:i:.+') . '\$ .*php -v.+' .
            'php ' . PHP_VERSION . '.+' .
            '123412.+/si',
            $log
        );
    }

    public function testKeyValidNestedCommands()
    {
        $_GET['key'] = '123';
        $_SERVER['HTTP_HOST'] = 'test.domain';
        $app = new DeployApplication('123', '.', $this->fileName);

        $this->assertFileNotExists($this->fileName);
        $app->execute([
            'echo testing_echo',
            'php' => '-v && echo 123456',
            'echo testing_echo',
            ['php' => '-v && echo 654123'],
            'echo testing_echo',
            ['php' => '-v && echo 147896']
        ]);
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp(
            '/.+ACCESS IS OBTAINED.+'

            . date('Y.m.d H:i:.+') . '\$ echo testing_echo.+testing_echo.+'
            . date('Y.m.d H:i:.+') . '\$ .*php -v.+php ' . PHP_VERSION . '.+123456.+'

            . date('Y.m.d H:i:.+') . '\$ echo testing_echo.+testing_echo.+'
            . date('Y.m.d H:i:.+') . '\$ .*php -v.+php ' . PHP_VERSION . '.+654123.+'

            . date('Y.m.d H:i:.+') . '\$ echo testing_echo.+testing_echo.+'
            . date('Y.m.d H:i:.+') . '\$ .*php -v.+php ' . PHP_VERSION . '.+147896.+/si',
            $log
        );
    }
}