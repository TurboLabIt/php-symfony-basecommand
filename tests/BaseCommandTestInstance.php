<?php declare(strict_types=1);
namespace TurboLabIt\BaseCommand\tests;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TurboLabIt\BaseCommand\Command\AbstractBaseCommand;
use TurboLabIt\BaseCommand\Service\Options;


#[AsCommand(name: 'TestInstance82')]
class BaseCommandTestInstance extends AbstractBaseCommand
{
    protected bool $allowDryRunOpt = true;
    protected bool $allowBlockMessagesOpt = true;
    protected bool $allowIdOpt = true;
    protected bool $allowLangOpt = false;

    const CLI_OPT_TRIGGER_ERROR     = 'trigger-error';

    const TEST_DIR_PATH             = '/tmp/BaseCommandTestInstance/';
    const FILE_ALWAYS_WRITE         = 'always-write';
    const FILE_NOT_DRY_RUN_WRITE    = 'not-dry-run';
    const FILE_ALWAYS_SEND_MSG      = 'always-send';
    const FILE_NOT_BLOCK_SEND_MSG   = 'send-not-blocked';
    const FILE_NO_ID_LIMIT          = 'no-id-limit_';
    const FILE_ID_LIMIT             = 'file-id-limit_';


    protected function configure()
    {
        parent::configure();
        $this->addOption(static::CLI_OPT_TRIGGER_ERROR, null, InputOption::VALUE_NONE, 'Trigger an error (for testing)');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        if( $this->getCliOption(static::CLI_OPT_TRIGGER_ERROR) ) {
            $this->endWithError('Error triggered via CLI');
            echo "🧨🧨 YOU SHOULDN'T SEE THIS 🧨🧨";
        }

        $this->fxTitle("Creating the tempWorkDir...");
        $tempWorkDirPath = $this->getTempWorkingDirPath();
        $this->fxOK('TempWorkingDirPath: ##' . $tempWorkDirPath . "##");

        $this->fxTitle("Display a testfile path...");
        $tempFile = $this->getTempWorkingDirFile(static::FILE_ALWAYS_WRITE);
        $this->fxOK('TempWorkingDirFile: ##' . $tempFile . "##");

        $this->fxTitle("Always writing a file...");
        file_put_contents( $this->getTempWorkingDirFile(static::FILE_ALWAYS_WRITE), "YES" );
        $this->fxOK();

        $this->fxTitle("Writing a file only if not " . Options::CLI_OPT_DRY_RUN . "...");
        if( $this->isNotDryRun() ) {

            file_put_contents( $this->getTempWorkingDirFile(static::FILE_NOT_DRY_RUN_WRITE), "YES");
            $this->fxOK();
        }

        $this->fxTitle("Always sending a message...");
        file_put_contents( $this->getTempWorkingDirFile(static::FILE_ALWAYS_SEND_MSG), "YES" );
        $this->fxOK();

        $this->fxTitle("Sending a message only if not " . Options::CLI_OPT_BLOCK_MESSAGES . "...");
        if( $this->isSendingMessageAllowed() ) {

            file_put_contents( $this->getTempWorkingDirFile(static::FILE_NOT_BLOCK_SEND_MSG), "YES" );
            $this->fxOK();
        }

        return
            $this
                ->fxTitle("Iterating over elements...")
                ->iterateOver(static::FILE_NO_ID_LIMIT)
                ->fxOK()

                ->fxTitle("Iterating over elements, but filtering on ##" . $this->getCliOption(Options::CLI_OPT_SINGLE_ID) . "##...")
                ->iterateOver(static::FILE_ID_LIMIT, $this->getCliOption(Options::CLI_OPT_SINGLE_ID))
                ->fxOK()

                ->endWithSuccess();
    }


    protected function iterateOver(string $fileBasePath, ?int $id = null) : static
    {
        $arrElements = array_fill(0, 5, 'test');

        foreach($arrElements as $key => $val) {

            if( empty($id) || $this->isIdFilterMatch($key) ) {

                $filePath = $this->getTempWorkingDirFile($fileBasePath .  $key);
                file_put_contents($filePath, "YES");
            }
        }

        return $this;
    }
}
