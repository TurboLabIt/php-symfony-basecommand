<?php
namespace TurboLabIt\BaseCommand\Traits;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


trait EnvTrait
{
    protected ParameterBagInterface $parameters;


    public function isProd() : bool
    {
        return $this->parameters->get("kernel.environment") === "prod";
    }


    public function isNotProd() : bool
    {
        return !$this->isProd();
    }


    public function isDev() : bool
    {
        return $this->parameters->get("kernel.environment") === "dev";
    }
}
