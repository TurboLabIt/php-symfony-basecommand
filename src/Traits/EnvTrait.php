<?php
namespace TurboLabIt\BaseCommand\Traits;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;


trait EnvTrait
{
    protected ParameterBagInterface $parameters;


    public function getEnv() : string
    {
        if( empty($this->parameters) ) {
            throw new RuntimeException(
                'Autowiring ParameterBagInterface $parameters is required before using BaseCommand env functions'
            );
        }

        return $this->parameters->get("kernel.environment")
    }


    public function isProd() : bool { return $this->getEnv() === "prod"; }
    public function isNotProd() : bool { return !$this->isProd(); }
    public function isDev() : bool { return $this->getEnv() === "dev"; }
    public function isDevOrTest() : bool { return in_array($this->getEnv(), ["dev", "test"]); }
}
