<?php
namespace TurboLabIt\BaseCommand\Traits;

use TurboLabIt\BaseCommand\Service\BashFx;


trait BashFxDirectTrait
{
    protected ?BashFx $bashFx;


    protected function fxTitle(string $message) : self
    {
        $this->bashFx->fxTitle($message);
        return $this;
    }


    protected function fxInfo(string $message) : self
    {
        $this->bashFx->fxInfo($message);
        return $this;
    }


    protected function fxOK(?string $message = null) : self
    {
        $this->bashFx->fxOK($message);
        return $this;
    }

    protected function fxWarning(string $message) : self
    {
        $this->bashFx->fxWarning($message);
        return $this;
    }
}
