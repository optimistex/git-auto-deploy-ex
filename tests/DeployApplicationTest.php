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
        $this->assertRegExp('/.+ACCESS IS OBTAINED.+Executing shell commands.+\$ git branch.+\$ git pull/si', $log);
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
            'Executing shell commands.+' .
            '\$ echo testing_echo.+' .
            'testing_echo.+' .
            '\$ .*php -v.+' .
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
            'php' => '-v'
        ]);
        $this->assertFileExists($this->fileName);
        $log = file_get_contents($this->fileName);
        $this->assertRegExp(
            '/.+ACCESS IS OBTAINED.+' .
            'Executing shell commands.+' .
            '\$ echo testing_echo.+' .
            'testing_echo.+' .
            '\$ .*php -v.+' .
            'php ' . PHP_VERSION . '.+/si',
            $log
        );
    }
}