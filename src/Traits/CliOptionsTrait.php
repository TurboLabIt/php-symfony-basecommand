<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;


trait CliOptionsTrait
{
    const CLI_OPT_DRY_RUN           = "dry-run";
    const CLI_OPT_BLOCK_MESSAGES    = "no-email";
    const CLI_OPT_SINGLE_ID         = "id";
    const CLI_OPT_NO_DOWNLOAD       = "no-download";
    const CLI_OPT_LANGUAGE          = "language";

    /**
     * Can multiple instances of this command run in parallel?
     * 💡 You shouldn't allow it, unless you explictely design the
     * command to support it
     */
    protected bool $allowParallelExec = false;

    /**
     * Does this command accept `--CLI_OPT_DRY_RUN`?
     * 💡 It's highly recommended!
     */
    protected bool $allowDryRunOpt = false;

    /**
     * Does this command accept `--CLI_OPT_BLOCK_MESSAGES`?
     * 💡 It's highly recommended if this commands sends
     * emails or messages!
     */
    protected bool $allowBlockMessagesOpt = false;

    /**
     * Does this command accept `--CLI_OPT_SINGLE_ID=<id>`?
     * 💡 It's highly recommended!
     */
    protected bool $allowIdOpt = false;

    /**
     * Allow the app to run with local, cached data, without any download?
     * 💡 If the app downloads data, you should enable it.
     */
    protected bool $allowNoDownloadOpt = false;

    /**
     * Does this command accept a language (`--CLI_OPT_LANGUAGE=<ln>`?)
     */
    protected bool $allowLangOpt = false;

    /**
     * Is the `--CLI_OPT_LANGUAGE=<ln>` mandatory?
     */
    protected bool $langOptIsMandatory = false;


    protected function configure()
    {
        parent::configure();

        if( $this->allowDryRunOpt ) {
            $this->addOption(static::CLI_OPT_DRY_RUN, null, InputOption::VALUE_NONE, 'Read-only test run. Don\'t really change anything');
        }

        if( $this->allowBlockMessagesOpt ) {
            $this->addOption(static::CLI_OPT_BLOCK_MESSAGES, null, InputOption::VALUE_NONE, 'Don\t send any emails or messages');
        }

        if( $this->allowIdOpt ) {
            $this->addOption(static::CLI_OPT_SINGLE_ID, null, InputOption::VALUE_REQUIRED, 'Process the item identified by this specific ID only');
        }

        if( $this->allowNoDownloadOpt ) {
            $this->addOption(static::CLI_OPT_NO_DOWNLOAD, null, InputOption::VALUE_NONE, 'Run with local, cached data, skipping any download');
        }

        if( $this->allowLangOpt ) {
            $this->addOption(static::CLI_OPT_LANGUAGE, null, InputOption::VALUE_REQUIRED, 'The language to work on');
        }
    }


    protected function checkOptions()
    {
        if( !$this->allowParallelExec && !$this->lock() ) {
            $this->endWithError('The command ##' . $this->getName() . '## is already running in another process');
        }

        if( $this->langOptIsMandatory && empty($this->getCliOption(static::CLI_OPT_LANGUAGE)) ) {
            $this->endWithError('The --' . static::CLI_OPT_LANGUAGE . "=... option is MANDATORY");
        }
    }


    protected function getCliOption(string $staticCLI_OPT_NAME) : mixed
    {
        return $this->input->getOption($staticCLI_OPT_NAME);
    }


    protected function isNotDryRun(bool $silent = false) : bool
    {
        $isDryRun = $this->getCliOption(static::CLI_OPT_DRY_RUN);

        if( $isDryRun && !$silent ) {
            $this->fxInfo("🦘 Skipped due to --" . static::CLI_OPT_DRY_RUN);
        }

        return !$isDryRun;
    }


    protected function isSendingMessageAllowed(bool $silent = false) : bool
    {
        $isMessagingBlocked = $this->getCliOption(static::CLI_OPT_BLOCK_MESSAGES);

        if( $isMessagingBlocked && !$silent ) {
            $this->fxInfo("🦘 Skipped due to --" . static::CLI_OPT_BLOCK_MESSAGES);
        }

        return !$isMessagingBlocked;
    }


    protected function warnIdFilterSet() : bool
    {
        if( !$this->allowIdOpt ) {
            return false;
        }

        $idOpt = $this->getCliOption(static::CLI_OPT_SINGLE_ID);
        if( $idOpt === null ) {
            return false;
        }

        $this->fxWarning("--" . static::CLI_OPT_SINGLE_ID . "=##$idOpt## is set!");
        return true;
    }


    protected function isIdFilterMatch(int $id, bool $silent = false) : bool
    {
        $idOpt = $this->getCliOption(static::CLI_OPT_SINGLE_ID);

        if( $idOpt === null ) {
            return true;
        }

        if( $id == $idOpt ) {

            $this->fxInfo("🎯 --" . static::CLI_OPT_SINGLE_ID . "=##$id##: HIT!");
            return true;
        }

        $this->fxInfo("🦘 ##$id## skipped due to --" . static::CLI_OPT_SINGLE_ID . "=##$idOpt##");
        return false;
    }


    protected function isDownloadAllowed(bool $silent = false) : bool
    {
        $isDownloadBlocked = $this->getCliOption(static::CLI_OPT_NO_DOWNLOAD);

        if( $isDownloadBlocked && !$silent ) {
            $this->fxInfo("🦘 Skipped due to --" . static::CLI_OPT_NO_DOWNLOAD);
        }

        return !$isDownloadBlocked;
    }
}
