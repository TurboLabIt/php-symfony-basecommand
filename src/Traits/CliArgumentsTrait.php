<?php
namespace TurboLabIt\BaseCommand\Traits;


trait CliArgumentsTrait
{
    protected function getCliArgument(string $staticCLI_ARG_NAME) : mixed
    {
        return $this->input->getArgument($staticCLI_ARG_NAME);
    }
}
