<?php
/**
 * BaseCommand by TurboLab.it
 * @see https://github.com/TurboLabIt/php-symfony-basecommand
 */
namespace TurboLabIt\BaseCommand\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TurboLabIt\BaseCommand\Service\DateMagician;
use TurboLabIt\BaseCommand\Service\Mailer;
use TurboLabIt\BaseCommand\Traits\BashFxDirectTrait;
use TurboLabIt\BaseCommand\Traits\EnvTrait;
use TurboLabIt\BaseCommand\Traits\ProjectDirDirectTrait;
use TurboLabIt\BaseCommand\Traits\CliOptionsTrait;
use TurboLabIt\BaseCommand\Traits\CliArgumentsTrait;
use TurboLabIt\BaseCommand\Traits\HeaderFooterTrait;
use TurboLabIt\BaseCommand\Traits\SpreadsheetTrait;
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


abstract class AbstractBaseCommand extends Command
{
    const WARNING = 9;

    use BashFxDirectTrait, ProjectDirDirectTrait, CliOptionsTrait, CliArgumentsTrait, HeaderFooterTrait, TempWorkDirTrait,
    CsvHandlerTrait, LockableTrait, IteratorTrait, ParsingTrait, EnvTrait, SpreadsheetTrait;

    protected DateMagician $dateMagician;

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
        $this->dateMagician     = new DateMagician();
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


    protected function autoInit() : static
    {
        return $this;
    }


    protected function checkEmailSending() : static
    {
        if( !$this->allowBlockMessagesOpt && !$this->allowSendMessagesOpt ) {
            return $this;
        }

        $sendingIsAllowed =
            $this
                ->fxTitle("📨 Messages sending status")
                ->isSendingMessageAllowed();

        if( !empty($this->mailer) && $this->mailer instanceof Mailer ) {
            $this->mailer->block( !$sendingIsAllowed );
        }

        return $this;
    }
}
