<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

use Symfony\Component\Console\Helper\ProgressBar;


trait IteratorTrait
{
    protected bool $iteratorWarnIfEmpty         = true;
    protected bool $iteratorWarnIfSingleIdOpt   = true;
    protected bool $iteratorWarnIfDryRun        = true;
    protected bool $iteratorAutoSkip            = true;


    protected function processItems(iterable $items, callable $fxProcess, ?int $itemNum = null, ?callable $fxGenerateItemTitle = null, ?callable $fxAutoSkipLogic = null) : self
    {
        if( $itemNum === null ) {
            $itemNum = count($items);
        }

        if( $itemNum === 0 && $this->iteratorWarnIfEmpty ) {
            $this->fxWarning("The dataset to iterate over is empty!");
        }

        if( $this->iteratorWarnIfSingleIdOpt ) {
            $this->warnIdFilterSet();
        }

        $this->isNotDryRun( !$this->iteratorWarnIfDryRun );

        if( $itemNum === 0 ) {
            return $this;
        }

        if( $fxGenerateItemTitle === null ) {
            $fxGenerateItemTitle = [$this, 'buildItemTitle'];
        }

        if( $fxAutoSkipLogic === null ) {
            $fxAutoSkipLogic = [$this, 'iteratorSkipCondition'];
        }

        ProgressBar::setFormatDefinition('custom',
            '%current%/%max% [%bar%] %percent:3s%% ⏳️ %elapsed:6s%/%estimated:-6s% 📈 %memory:6s%' . PHP_EOL . '👉️ %message%' . PHP_EOL
        );

        $progressBar = new ProgressBar($this->output, $itemNum);
        $progressBar->setBarCharacter('<fg=green>-</>');
        $progressBar->setProgressCharacter("ᗧ");
        $progressBar->setEmptyBarCharacter("<fg=red>•</>");
        $progressBar->setFormat('custom');
        $progressBar->setRedrawFrequency(1);
        $progressBar->maxSecondsBetweenRedraws(0);
        $progressBar->minSecondsBetweenRedraws(0);

        $progressBar->setMessage("🏁 Starting...");
        $progressBar->start();

        foreach($items as $key => $item) {

            $title = $fxGenerateItemTitle($key, $item);
            $progressBar->setMessage($title);
            $progressBar->advance();

            if( $this->iteratorAutoSkip && $fxAutoSkipLogic($key, $item) ) {
                continue;
            }

            $fxProcess($key, $item, $title, $itemNum, $progressBar, $items);
        }

        $progressBar->finish();
        $this->io->newLine(2);

        $this->fxOK("♾️  Done");

        return $this;
    }


    protected function buildItemName($key, $item) : string
    {
        $txtName = '';

        if( is_object($item) ) {

            foreach(['getName', 'getTitle'] as $method) {
                if( method_exists($item, $method) ) {
                    $txtName = $item->$method();
                    break;
                }
            }

        } elseif( is_array($item) ) {

            foreach(['name', 'Name', 'title', 'Title'] as $key) {
                if( !empty($item[$key]) ) {
                    $txtName = $item[$key];
                    break;
                }
            }

        } else {

            $txtName = $item;
        }

        $txtName = trim($txtName);
        return $txtName;
    }


    protected function buildItemTitle($key, $item) : string
    {
        $txtTitle = '';

        if( !empty($key) ) {
            $txtTitle = "[$key] ";
        }

        $txtTitle .= $this->buildItemName($key, $item);

        trim($txtTitle);
        return $txtTitle;
    }


    protected function iteratorSkipCondition($key, $item) : bool
    {
        $isIdFilterMatch = $this->isIdFilterMatch($key, true);
        return !$isIdFilterMatch;
    }
}
