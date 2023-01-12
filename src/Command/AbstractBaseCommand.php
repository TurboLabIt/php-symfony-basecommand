<?php
/**
 * BaseCommand by TurboLab.it
 * @see https://github.com/TurboLabIt/php-symfony-basecommand
 */
namespace TurboLabIt\PhpSymfonyBasecommand\Command;

use Symfony\Component\Console\Command\Command;
use TurboLabIt\PhpSymfonyBasecommand\Traits\BashFxDirectTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\CliOptionsTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\CliArgumentsTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\HeaderFooterTrait;
use TurboLabIt\PhpSymfonyBasecommand\Traits\TempWorkDirTrait;
use Symfony\Component\Console\Command\LockableTrait;
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
    use LockableTrait;

    protected array $arrConfig;

    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;


    public function __construct(array $arrConfig = [], ?BashFx $bashFx = null)
    {
        parent::__construct();
        $this->arrConfig    = $arrConfig;
        $this->bashFx       = $bashFx ?? (new BashFx());
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
