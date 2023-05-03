<?php
/**
 * BaseCommand by TurboLab.it
 * @see https://github.com/TurboLabIt/php-symfony-basecommand
 */
namespace TurboLabIt\PhpSymfonyBasecommand\Command;

use Symfony\Component\Console\Command\Command;
use TurboLabIt\PhpSymfonyBasecommand\Service\ItemStringify;
use TurboLabIt\PhpSymfonyBasecommand\Traits\BashFxDirectTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\CliOptionsTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\CliArgumentsTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\HeaderFooterTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\TempWorkDirTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\CsvHandlerTrait;
use Symfony\Component\Console\Command\LockableTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\IteratorTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\ParsingTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TurboLabIt\PhpSymfonyBasecommand\Service\BashFx;


abstract class AbstractBaseCommand extends Command
{
    use BashFxDirectTrait;
    use CliOptionsTrait;
    use CliArgumentsTrait;
    use HeaderFooterTrait;
    use TempWorkDirTrait;
    use CsvHandlerTrait;
    use LockableTrait;
    use IteratorTrait;
    use ParsingTrait;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;

    protected array $arrReport = [];


    public function __construct(
        protected array $arrConfig = [],
        ?BashFx $bashFx = null, ?ItemStringify $itemStringify = null
    )
    {
        parent::__construct();
        $this->bashFx           = $bashFx ?? (new BashFx());
        $this->itemStringify    = $itemStringify ?? (new ItemStringify());
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
        ;

        return static::SUCCESS;
        // 💡 the extending class must `return $this->showEnd();`
    }


    protected function autoInit() : self
    {
        return $this;
    }
}
