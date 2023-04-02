<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

use TurboLabIt\PhpSymfonyBasecommand\Command\AbstractBaseCommand;

trait HeaderFooterTrait
{
    protected function showStart() : self
    {
        $this->bashFx->fxHeader("🚀 Running ##" .  $this->getName() . "##");
        return $this;
    }


    protected function endWithSuccess() : int
    {
        return
            $this
                ->showEndReport()
                ->bashFx->fxEndFooter( static::SUCCESS, $this->getName() );
    }


    protected function endWithError(string $message) : void
    {
        $this->bashFx->fxCatastrophicError($message);
    }


    protected function addToReport($key, $newValue) : bool
    {
        if( in_array($newValue, $this->arrReport[$key]) ) {
            return false;
        }

        $this->arrReport[$key][] = $newValue;
        return true;
    }


    protected function showEndReport() : self
    {
        if( empty($this->arrReport) ) {
            return $this;
        }

        return
            $this
                ->fxTitle("📊 Report")
                ->executionReport();
    }


    protected function executionReport() : self
    {
        $arrDefinitionList = [];
        foreach($this->arrReport as $key => $value) {

            if( is_array($value) ) {
                $value = implode('##, ##', $value);
            }

            $arrDefinitionList[] = [ $key => $value ];
        }

        $this->io->definitionList(...$arrDefinitionList);

        return $this;
    }
}
