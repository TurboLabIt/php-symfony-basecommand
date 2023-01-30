<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

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
                ->bashFx->fxEndFooter();
    }


    protected function endWithError(string $message) : void
    {
        $this->bashFx->fxCatastrophicError($message);
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
        // customize
        return $this;
    }
}
