<?php
use TurboLabIt\PhpSymfonyBasecommand\tests\BaseCommandTestInstance;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

$arrCmdArguments = [
    "--" . BaseCommandTestInstance::CLI_OPT_DRY_RUN         => true,
    "--" . BaseCommandTestInstance::CLI_OPT_BLOCK_MESSAGES  => true,
    "--" . BaseCommandTestInstance::CLI_OPT_SINGLE_ID       => 3,
];

( new BaseCommandTestInstance() )
    ->setName('TestInstance')
    ->run(new ArrayInput($arrCmdArguments), new ConsoleOutput());

    
$arrCmdArguments = [
    "--" . BaseCommandTestInstance::CLI_OPT_TRIGGER_ERROR => true
];

( new BaseCommandTestInstance() )
    ->setName('TestInstance')
    ->run(new ArrayInput($arrCmdArguments), new ConsoleOutput());
