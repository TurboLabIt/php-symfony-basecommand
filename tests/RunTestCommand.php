<?php

use TurboLabIt\BaseCommand\Service\BashFx;
use TurboLabIt\BaseCommand\tests\BaseCommandTestInstance;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use \TurboLabIt\BaseCommand\Service\Options;

require __DIR__.'/../vendor/autoload.php';

$arrCmdArguments = [
    "--" . Options::CLI_OPT_DRY_RUN         => true,
    "--" . Options::CLI_OPT_BLOCK_MESSAGES  => true,
    "--" . Options::CLI_OPT_SINGLE_ID       => 3,
];

( new BaseCommandTestInstance([], new BashFx()) )
    ->setName('TestInstance')
    ->run(new ArrayInput($arrCmdArguments), new ConsoleOutput());


$arrCmdArguments = [
    "--" . BaseCommandTestInstance::CLI_OPT_TRIGGER_ERROR => true
];

( new BaseCommandTestInstance([], new BashFx()) )
    ->setName('TestInstance')
    ->run(new ArrayInput($arrCmdArguments), new ConsoleOutput());
