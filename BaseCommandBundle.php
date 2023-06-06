<?php
namespace TurboLabIt\BaseCommand;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class BaseCommandBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        die("STOP FUUUUCK!!");
        $containerConfigurator->import('../config/services.xml');
    }
}
