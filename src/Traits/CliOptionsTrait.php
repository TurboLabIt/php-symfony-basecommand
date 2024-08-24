<?php
namespace TurboLabIt\BaseCommand\Traits;

use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use TurboLabIt\BaseCommand\Service\Options;


trait CliOptionsTrait
{
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

    /**
     * Should the application work in a restricted mode or
     * on a limited dataset by default and unlock the full
     * mode only if the `--CLI_OPT_UNLOCK` is provided?
     * 💡 This is reccomended for commands designed to
     * send newsletters or deliver messages to the users
     */
    protected bool $limitedByDefaultOpt = false;

    /**
     * Should the unlimited mode by `--CLI_OPT_UNLOCK`
     * be allowed on a limited set of enviornments only?
     */
    protected array $allowUnlockOptIn = ["prod"];


    protected function configure() : void
    {
        parent::configure();

        if( $this->allowDryRunOpt ) {
            $this->addOption(Options::CLI_OPT_DRY_RUN, null, InputOption::VALUE_NONE, 'Read-only test run. Don\'t really change anything');
        }

        if( $this->allowBlockMessagesOpt ) {
            $this->addOption(Options::CLI_OPT_BLOCK_MESSAGES, null, InputOption::VALUE_NONE, 'Don\t send any emails or messages');
        }

        if( $this->allowIdOpt ) {
            $this->addOption(Options::CLI_OPT_SINGLE_ID, null, InputOption::VALUE_REQUIRED, 'Process the item identified by this specific ID only');
        }

        if( $this->allowNoDownloadOpt ) {
            $this->addOption(Options::CLI_OPT_NO_DOWNLOAD, null, InputOption::VALUE_NONE, 'Run with local, cached data, skipping any download');
        }

        if( $this->allowLangOpt ) {
            $this->addOption(Options::CLI_OPT_LANGUAGE, null, InputOption::VALUE_REQUIRED, 'The language to work on');
        }

        if( $this->limitedByDefaultOpt ) {
            $this->addOption(Options::CLI_OPT_UNLOCK, null, InputOption::VALUE_NONE, 'Remove all restrictions');
        }
    }


    protected function checkOptions()
    {
        if( !$this->allowParallelExec && !$this->lock() ) {
            $this->endWithError('The command ##' . $this->getName() . '## is already running in another process');
        }

        if( $this->langOptIsMandatory && empty($this->getCliOption(Options::CLI_OPT_LANGUAGE)) ) {
            $this->endWithError('The --' . Options::CLI_OPT_LANGUAGE . "=... option is MANDATORY");
        }

        return $this;
    }


    protected function getCliOption(string $staticCLI_OPT_NAME) : mixed
        { return $this->input->getOption($staticCLI_OPT_NAME); }


    protected function getCliId() : mixed
    {
        if( !$this->allowIdOpt ) {
            return null;
        }

        return $this->getCliOption(Options::CLI_OPT_SINGLE_ID);
    }


    protected function isDryRun(bool $silent = false) : bool
    {
        if( !$this->allowDryRunOpt ) {
            return false;
        }

        $isDryRun = $this->getCliOption(Options::CLI_OPT_DRY_RUN);

        if( $isDryRun && !$silent ) {
            $this->fxWarning("🧪 --" . Options::CLI_OPT_DRY_RUN . " is active");
        }

        return $isDryRun;
    }

    protected function isNotDryRun(bool $silent = false) : bool
        { return !$this->isDryRun($silent); }


    protected function isSendingMessageAllowed(bool $silent = false) : bool
    {
        $isMessagingBlocked = $this->getCliOption(Options::CLI_OPT_BLOCK_MESSAGES);

        if( $isMessagingBlocked && !$silent ) {
            $this->fxWarning("🦘 Skipped due to --" . Options::CLI_OPT_BLOCK_MESSAGES);
        }

        return !$isMessagingBlocked;
    }


    protected function warnIdFilterSet() : bool
    {
        if( !$this->allowIdOpt ) {
            return false;
        }

        $idOpt = $this->getCliOption(Options::CLI_OPT_SINGLE_ID);
        if( $idOpt === null ) {
            return false;
        }

        $this->fxWarning("--" . Options::CLI_OPT_SINGLE_ID . "=##$idOpt## is set!");
        return true;
    }


    protected function isIdFilterMatch($id, bool $silent = false) : bool
    {
        if( !$this->allowIdOpt ) {
            return true;
        }

        $idOpt = $this->getCliOption(Options::CLI_OPT_SINGLE_ID);

        if( $idOpt === null ) {
            return true;
        }

        if( $id == $idOpt && !$silent) {

            $this->fxWarning("🎯 --" . Options::CLI_OPT_SINGLE_ID . "=##$id##: HIT!");
            return true;
        }

        if( $id == $idOpt ) {
            return true;
        }

        if( !$silent ) {
            $this->fxWarning("🦘 ##$id## skipped due to --" . Options::CLI_OPT_SINGLE_ID . "=##$idOpt##");
        }

        return false;
    }


    protected function isDownloadAllowed(bool $silent = false) : bool
    {
        $isDownloadBlocked = $this->getCliOption(Options::CLI_OPT_NO_DOWNLOAD);

        if( $isDownloadBlocked && !$silent ) {
            $this->fxInfo("🦘 Skipped due to --" . Options::CLI_OPT_NO_DOWNLOAD);
        }

        return !$isDownloadBlocked;
    }


    protected function isLimited(bool $silent = false) : bool
    {
        if( !$this->limitedByDefaultOpt ) {
            return false;
        }

        $isLimited = !$this->getCliOption(Options::CLI_OPT_UNLOCK);

        if(
            !$isLimited && !empty($this->allowUnlockOptIn) &&
            !in_array($this->getEnv(), $this->allowUnlockOptIn)
        ) {
            $fullMessage =
                "Can't use --" . Options::CLI_OPT_UNLOCK . " in " . $this->getEnv() . " env, only in " .
                implode(', ', $this->allowUnlockOptIn);
            
            throw new RuntimeException($fullMessage);
        }

        if( $isLimited && !$silent ) {
            $this->fxWarning("🧪 Limited mode is engaged. Remove it with --" . Options::CLI_OPT_UNLOCK);
        }
        
        if( !$isLimited && !$silent ) {
            $this->fxWarning("⚠ Unlimited mode is engaged via --" . Options::CLI_OPT_UNLOCK);
        }

        return $isLimited;
    }

    protected function isUnlocked(bool $silent = false) : bool { return !$this->isLimited($silent); }
}
