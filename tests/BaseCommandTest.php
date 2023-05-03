<?php declare(strict_types=1);
namespace TurboLabIt\PhpSymfonyBasecommand\tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TurboLabIt\PhpSymfonyBasecommand\Service\Options;


class BaseCommandTest extends TestCase
{
    use TestSuiteSupportFxTrait;

    protected function setUp() : void
    {
        $this->deleteDirectory( $this->getTempWorkingDirPath() );
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
            Options::CLI_OPT_DRY_RUN, Options::CLI_OPT_BLOCK_MESSAGES,
            Options::CLI_OPT_SINGLE_ID
        ];

        foreach($arrExpectedOpions as $opt) {
            $this->assertContains($opt, $arrOptions);
        }
    }


    public function testTempWorkingDirPath()
    {
        $output = $this->runCommandAndGetOutput();
        $path   = $this->getTempWorkingDirPath();

        $message = 'TempWorkingDirPath: ##' . $path . "##";
        $this->assertStringContainsString($message, $output);
    }


    public function testTempWorkingDirFile()
    {
        $output = $this->runCommandAndGetOutput();
        $path   = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ALWAYS_WRITE);

        $message = 'TempWorkingDirFile: ##' . $path . "##";
        $this->assertStringContainsString($message, $output);
    }


    public function testNotDryRun()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ALWAYS_WRITE);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NOT_DRY_RUN_WRITE);
        $this->assertFileExists($filePathNotDryRun);
    }


    public function testDryRun()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . Options::CLI_OPT_DRY_RUN => true]);

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ALWAYS_WRITE);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NOT_DRY_RUN_WRITE);
        $this->assertFileDoesNotExist($filePathNotDryRun);
    }


    public function testNotSendMessageBlocked()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ALWAYS_SEND_MSG);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NOT_BLOCK_SEND_MSG);
        $this->assertFileExists($filePathNotDryRun);
    }


    public function testSendMessageBlocked()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . Options::CLI_OPT_BLOCK_MESSAGES => true]);

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ALWAYS_SEND_MSG);
        $this->assertFileExists($filePathAlways);

        $filePathNotDryRun = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NOT_BLOCK_SEND_MSG);
        $this->assertFileDoesNotExist($filePathNotDryRun);
    }


    public function testNoIdLimit()
    {
        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd);

        $arrElements = array_fill(0, 5, 'test');

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NO_ID_LIMIT);
        $this->testId($arrElements, $filePathAlways, true);

        $filePathIdLimit = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ID_LIMIT);
        $this->testId($arrElements, $filePathIdLimit, true);
    }


    public function testIdLimit()
    {
        $processThisIdOnly = 3;

        $cmd        = $this->getCommandInstance();
        $cmdReturn  = $this->runCommand($cmd, ["--" . Options::CLI_OPT_SINGLE_ID => $processThisIdOnly]);

        $arrElements = array_fill(0, 5, 'test');

        $filePathAlways = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_NO_ID_LIMIT);
        $this->testId($arrElements, $filePathAlways, true);

        $filePathIdLimit = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ID_LIMIT);
        $this->testId([$processThisIdOnly => 'test'], $filePathIdLimit, true);

        $arrElementsFilteredOut = $arrElements;
        unset($arrElementsFilteredOut[$processThisIdOnly]);

        $filePathIdLimit = $this->getTempWorkingDirFile(BaseCommandTestInstance::FILE_ID_LIMIT);
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
