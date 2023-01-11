<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

use Symfony\Component\Console\Input\InputArgument;


trait CliArgumentsTrait
{
    protected function getCliOption(string $staticCLI_ARG_NAME) : mixed
    {
        return $this->input->getArgument($staticCLI_ARG_NAME);
    }
}
