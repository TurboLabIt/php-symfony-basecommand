<?php
/**
 * BaseCommand by TurboLab.it
 * @see https://github.com/TurboLabIt/php-symfony-basecommand
 */
namespace TurboLabIt\BaseCommand\Command;

use Symfony\Component\Console\Command\Command;
use TurboLabIt\BaseCommand\Service\Mailer;
use TurboLabIt\BaseCommand\Service\Options;
use TurboLabIt\BaseCommand\Traits\BashFxDirectTrait;
use TurboLabIt\BaseCommand\Traits\ProjectDirDirectTrait;
use TurboLabIt\BaseCommand\Traits\CliOptionsTrait;
use TurboLabIt\BaseCommand\Traits\CliArgumentsTrait;
use TurboLabIt\BaseCommand\Traits\HeaderFooterTrait;
use TurboLabIt\BaseCommand\Traits\TempWorkDirTrait;
use TurboLabIt\BaseCommand\Traits\CsvHandlerTrait;
use Symfony\Component\Console\Command\LockableTrait;
use TurboLabIt\BaseCommand\Traits\IteratorTrait;
use TurboLabIt\BaseCommand\Traits\ParsingTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TurboLabIt\BaseCommand\Service\BashFx;
use TurboLabIt\BaseCommand\Service\ItemStringify;
use TurboLabIt\BaseCommand\Service\ProjectDir;
use TurboLabIt\BaseCommand\Service\Dates;


abstract class AbstractBaseCommand extends Command
{
    use BashFxDirectTrait;
    use ProjectDirDirectTrait;
    use CliOptionsTrait;
    use CliArgumentsTrait;
    use HeaderFooterTrait;
    use TempWorkDirTrait;
    use CsvHandlerTrait;
    use LockableTrait;
    use IteratorTrait;
    use ParsingTrait;

    protected Dates $dates;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;

    protected array $arrReport = [];


    public function __construct(
        protected array $arrConfig = [],
        protected ?BashFx $bashFx = null, protected ?ItemStringify $itemStringify = null
    )
    {
        parent::__construct();
        $this->bashFx           = $bashFx ?? (new BashFx());
        $this->itemStringify    = $itemStringify ?? (new ItemStringify());
        $this->dates            = new Dates();
    }


    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // 💡 the extending class must `parent::execute($input, $output)`;

        $this->input    = $input;
        $this->output   = $output;
        $this->io       = $this->bashFx->setIo($input, $output);

        $this->showStart();

        ini_set('memory_limit', -1);

        $this
            ->autoInit()
            ->checkOptions()
            ->checkEmailSending();

        return static::SUCCESS;
        // 💡 the extending class must `return $this->showEnd();`
    }


    protected function autoInit() : self
    {
        return $this;
    }


    protected function checkEmailSending() : static
    {
        if( $this->allowBlockMessagesOpt == false ) {
            return $this;
        }

        $this->fxTitle("📨 Email status...");

        $isTliMailerInstance = !empty($this->mailer) && $this->mailer instanceof Mailer;

        if( $this->isSendingMessageAllowed() ) {

            if($isTliMailerInstance) {
                $this->mailer->block(false);
            }

            return $this->fxWarning("📨🔥 Emails are HOT!");
        }

        if($isTliMailerInstance) {

            $this->mailer->block(true);
            return $this->fxInfo("Email are blocked");
        }

        return $this->fxInfo("Email SHOULD be blocked");
    }
}
