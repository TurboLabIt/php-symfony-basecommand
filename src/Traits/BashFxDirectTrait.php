<?php
namespace TurboLabIt\BaseCommand\Traits;

use TurboLabIt\BaseCommand\Service\BashFx;


trait BashFxDirectTrait
{
    protected ?BashFx $bashFx;


    protected function fxTitle(string $message) : static
    {
        $this->bashFx->fxTitle($message);
        return $this;
    }


    protected function fxInfo(string $message) : static
    {
        $this->bashFx->fxInfo($message);
        return $this;
    }


    protected function fxOK(?string $message = null) : static
    {
        $this->bashFx->fxOK($message);
        return $this;
    }

    protected function fxWarning(string $message) : static
    {
        $this->bashFx->fxWarning($message);
        return $this;
    }

    protected function fxListFiles(string $path, string $orderBy = BashFx::ORDER_BY_NAME) : BashFx
    {
        return $this->bashFx->fxListFiles($path, $orderBy);
    }
}
