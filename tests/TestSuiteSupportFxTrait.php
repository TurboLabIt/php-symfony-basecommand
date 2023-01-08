<?php declare(strict_types=1);
namespace TurboLabIt\PhpSymfonyBasecommand\tests;

use TurboLabIt\PhpSymfonyBasecommand\Service\BashFx;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;


trait TestSuiteSupportFxTrait
{
    protected function getCommandInstance() : BaseCommandTestInstance
    {
        $cmd = new BaseCommandTestInstance( new BashFx() );
        $cmd->setName('TestInstance99');
        return $cmd;
    }


    protected function runCommand(BaseCommandTestInstance $cmd = null, array $arrCmdArguments = [], bool $assertCmdSuccess = true) : int
    {
        if( empty($cmd) ) {
            $cmd = $this->getCommandInstance();
        }

        $arrParams = new ArrayInput($arrCmdArguments);
        $result = $cmd->run($arrParams, new NullOutput());

        if($assertCmdSuccess) {
            $this->assertEquals($result, BaseCommandTestInstance::SUCCESS);
        }

        return $result;
    }


    protected function getCommandTestInstance() : CommandTester
    {
        $cmd = $this->getCommandInstance();
        // https://symfony.com/doc/current/console.html#testing-commands
        return new CommandTester($cmd);
    }


    protected function runCommandAndGetOutput(array $arrCmdArguments = [], bool $assertCmdSuccess = true) : string
    {
        $cmd = $this->getCommandTestInstance();
        $cmd->execute($arrCmdArguments);

        if($assertCmdSuccess) {
            $cmd->assertCommandIsSuccessful();
        }
        
        $output = $cmd->getDisplay();
        return $output;
    }


    protected function testId(array $arrValues, string $fileBasePath, bool $mustExist) : self
    {
        foreach($arrValues as $key => $val) {

            $filePath = $fileBasePath . $key;

            if($mustExist) {

                $this->assertFileExists($filePath);

            } else {

                $this->assertFileDoesNotExist($filePath);
            }
        }

        return $this;
    }


    protected function deleteDirectory($dir) : bool
    {
        if ( !file_exists($dir) ) {
            return true;
        }
    
        if ( !is_dir($dir) ) {
            return unlink($dir);
        }
    
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
    
            if ( !$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item) ) {
                return false;
            }
        }
    
        return rmdir($dir);
    }
}
