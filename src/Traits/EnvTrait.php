<?php
namespace TurboLabIt\BaseCommand\Traits;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;


trait EnvTrait
{
    protected ParameterBagInterface $parameters;


    public function isProd() : bool { return $this->getEnv() === "prod"; }

    public function isNotProd() : bool { return !$this->isProd(); }

    public function isDevOrTest() : bool { return $this->isDev() || $this->isTest(); }

    public function isDev() : bool { return $this->getEnv() === "dev"; }

    public function isTest() : bool { return $this->getEnv() === "test"; }


    public function getEnv() : string
    {
        if( $this instanceof AbstractController ) {
            $this->getParameter("kernel.environment");
        }

        if( empty($this->parameters) ) {
            throw new RuntimeException(
                'Autowiring ParameterBagInterface $parameters is required to use BaseCommand env functions'
            );
        }

        return $this->parameters->get("kernel.environment");
    }


    public function getEnvTag(bool $includeProd = false) : string
    {
        if( $this->isProd() && !$includeProd ) {
            return '';
        }

        return "[" . strtoupper( $this->getEnv() ) . "] ";
    }
}
