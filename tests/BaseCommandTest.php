<?php declare(strict_types=1);
namespace TurboLabIt\PhpSymfonyBasecommand\tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TurboLabIt\PhpSymfonyBasecommand\Command\AbstractBaseCommand;


class BaseCommandTest extends TestCase
{
    use TestSuiteSupportFxTrait;

    protected function setUp() : void
    {
        $this->deleteDirectory(BaseCommandTestInstance::TEST_DIR_PATH);
        mkdir(BaseCommandTestInstance::TEST_DIR_PATH);
    }


    public function testBaseCommandCanBeExtended()
    {
        $cmd = $this->getCommandInstance();
        $this->assertInstanceOf(BaseCommandTestInstance::class, $cmd);
    }


    public function testHeader()
    {
        $output = $this->runCommandAndGetOutput();
        $message = '🚀 Running ##' . $this->getCommandInstance()->getName() . '##';
        $this->assertStringContainsString($message, $output);
    }


    public function testTitle()
    {
        $output = $this->runCommandAndGetOutput();
        $message = "Writing a file only if not dry-run..." . PHP_EOL . "-------------------------------------";
        $this->assertStringContainsString($message, $output);
    }


    public function testListAvailableOptions()
    {
        $cmd = $this->getCommandInstance();
        $arrOptions = array_keys( $cmd->getDefinition()->getOptions() );
        $arrExpectedOpions = [
            AbstractBaseCommand::CLI_OPT_DRY_RUN, AbstractBaseCommand::CLI_OPT_BLOCK_MESSAGES,
            AbstractBaseCommand::CLI_OPT_SINGLE_ID
        ];

        foreach($arrExpectedOpions as $opt) {
            $this->assertContains($opt, $arrOptions);
        }
    }


    public function testNotDryRun()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_ALWAYS_WRITE);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $cmd->getTestPath(BaseCommandTestInstance::FILE_NOT_DRY_RUN_WRITE);
        $this->assertFileExists($filePathNotDryRun);
    }


    public function testDryRun()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . BaseCommandTestInstance::CLI_OPT_DRY_RUN => true]);

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_ALWAYS_WRITE);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $cmd->getTestPath(BaseCommandTestInstance::FILE_NOT_DRY_RUN_WRITE);
        $this->assertFileDoesNotExist($filePathNotDryRun);
    }


    public function testNotSendMessageBlocked()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_ALWAYS_SEND_MSG);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $cmd->getTestPath(BaseCommandTestInstance::FILE_NOT_BLOCK_SEND_MSG);
        $this->assertFileExists($filePathNotDryRun);
    }


    public function testSendMessageBlocked()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . BaseCommandTestInstance::CLI_OPT_BLOCK_MESSAGES => true]);

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_ALWAYS_SEND_MSG);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $cmd->getTestPath(BaseCommandTestInstance::FILE_NOT_BLOCK_SEND_MSG);
        $this->assertFileDoesNotExist($filePathNotDryRun);
    }


    public function testNoIdLimit()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $arrElements = array_fill(0, 5, 'test');

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_NO_ID_LIMIT);
        $this->testId($arrElements, $filePathAlways, true);

        $filePathIdLimit = $cmd->getTestPath(BaseCommandTestInstance::FILE_ID_LIMIT);
        $this->testId($arrElements, $filePathIdLimit, true);
    }


    public function testIdLimit()
    {
        $processThisIdOnly = 3;

        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . BaseCommandTestInstance::CLI_OPT_SINGLE_ID => $processThisIdOnly]);

        $arrElements = array_fill(0, 5, 'test');

        $filePathAlways = $cmd->getTestPath(BaseCommandTestInstance::FILE_NO_ID_LIMIT);
        $this->testId($arrElements, $filePathAlways, true);

        $filePathIdLimit = $cmd->getTestPath(BaseCommandTestInstance::FILE_ID_LIMIT);
        $this->testId([$processThisIdOnly => 'test'], $filePathIdLimit, true);

        $arrElementsFilteredOut = $arrElements;
        unset($arrElementsFilteredOut[$processThisIdOnly]);

        $filePathIdLimit = $cmd->getTestPath(BaseCommandTestInstance::FILE_ID_LIMIT);
        $this->testId($arrElementsFilteredOut, $filePathIdLimit, false);
    }


    public function testFooter()
    {
        $output = $this->runCommandAndGetOutput();
        $this->assertStringContainsString('🏁 The End 🏁 | OK', $output);
        $this->assertStringNotContainsString('🏁 The End 🏁 | KO', $output);
        $this->assertStringNotContainsString('Catastrophic error', $output);
    }


    public function testError()
    {
        $this->expectException(RuntimeException::class);

        $output = 
            $this->runCommandAndGetOutput([
                "--" . BaseCommandTestInstance::CLI_OPT_TRIGGER_ERROR => true
            ], false);

        $this->assertStringContainsString('Catastrophic error', $output);
        $this->assertStringContainsString('🏁 The End 🏁 | KO', $output);
        $this->assertStringNotContainsString('🏁 The End 🏁 | OK', $output);
        $this->assertStringNotContainsString("YOU SHOULDN'T SEE THIS", $output);
    }
}
