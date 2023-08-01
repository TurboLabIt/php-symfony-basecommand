# Symfony BaseCommand

An extension of Symfony Console Command to build your own CLI commands better and faster.

<p align="center">
  <img src="https://i.postimg.cc/FHBfDbJN/z-Shot-1673219261.png" />
</p>


## 🚀 Start your project (without Symfony)

If you are building a simple command and don't want the whole Symfony framework:

````shell
composer init

````

Install the package (see: [Install it with composer](https://github.com/TurboLabIt/php-symfony-basecommand#-install-it-with-composer))

Use this template to generate a `MyApp.php` bootstrap file:

````php
<?php
use MyVendorName\MyApp\MyAppNameCommand;
use TurboLabIt\BaseCommand\Command\AbstractBaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$arrCmdArguments = [
    MyAppNameCommand::CLI_ARG_MY_ARG => $argv[1],
    // 💡 https://github.com/TurboLabIt/php-symfony-basecommand/blob/main/src/Traits/CliOptionsTrait.php
    "--" . \TurboLabIt\BaseCommand\Service\Options::CLI_OPT_DRY_RUN         => true,
    //"--" . \TurboLabIt\BaseCommand\Service\Options::CLI_OPT_BLOCK_MESSAGES  => true,
];

( new MyAppNameCommand() )
    ->setName('MyAppName')
    ->run(new ArrayInput($arrCmdArguments), new ConsoleOutput());

````

Add a `run.sh` for easier execution:

````shell
#!/usr/bin/env bash

## https://github.com/TurboLabIt/webstackup/blob/master/script/base.sh
source "/usr/local/turbolab.it/webstackup/script/base.sh"
fxHeader "🚀 My App"
EXPECTED_USER=$(logname)

cd $PROJECT_DIR

wsuComposer install

php MyApp.php MyArg1

fxEndFooter

````


## 📦 Install it with composer

````bash
symfony composer config repositories.turbolabit/php-symfony-basecommand git https://github.com/TurboLabIt/php-symfony-basecommand.git
symfony composer require turbolabit/php-symfony-basecommand:dev-main

````


## 🚀 A template for your own Command

You can now use this template to build your own CLI app.

```php
<?php declare(strict_types=1);
namespace App\Command;

use TurboLabIt\BaseCommand\Command\AbstractBaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'MyCommand')]
class MyCommand extends AbstractBaseCommand
{
    // 💡 define your own specific --option(s)
    const CLI_OPT_MY_OPT = "my-opt";

    // 💡 set your `$allow` options: https://github.com/TurboLabIt/php-symfony-basecommand/blob/main/src/Traits/CliOptionsTrait.php
    protected bool $allowParallelExec     = ????;
    protected bool $allowDryRunOpt        = ????;
    protected bool $allowBlockMessagesOpt = ????;
    protected bool $allowIdOpt            = ????;
    protected bool $allowNoDownloadOpt    = ????;
    protected bool $allowLangOpt          = ????;
    protected bool $langOptIsMandatory    = ????;
    
    
    public function __construct(array $arrConfig = [])
    {
        parent::__construct($arrConfig);
        // 💡 inject your own dependencies
    }


    protected function configure()
    {
        parent::configure();
        // 💡 add your own specific option(s)
        $this->addOption(static::CLI_OPT_MY_OPT, null, InputOption::VALUE_NONE, 'Text description');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        // 💡 see https://github.com/TurboLabIt/php-symfony-basecommand/blob/main/src/Command/AbstractBaseCommand.php
        
        
        $this->fxTitle("Doing things...");
        // ...
        $this->fxOK();
        // 💡 see other output functions here: https://github.com/TurboLabIt/php-symfony-basecommand/blob/main/src/Traits/BashFxDirectTrait.php
        
        
        // 💡 the **smallest** section of data-changing ops must be wrapped like this
        $this->fxTitle("Changing some data...");
        if( $this->isNotDryRun() ) {
          
          // ...
          $this->fxInfo("Some minor detail you should know");
          $this->fxOK();
        }


        // 💡 the **smallest** section of email/message-sending ops must be wrapped like this
        $this->fxTitle("Sending the report to the manager...");
        if( $this->isSendingMessageAllowed() ) {
          
          // ...
          $this->fxInfo("Some minor detail you should know");
          $this->fxOK();
        }
        
        
        $this->fxTitle("Processing data...");
        foreach($this->arrData as $item) {
          
          $id = $item->getId();
          
          // 💡 use a guard clause to exclude IDs
          if ( !$this->isIdFilterMatch() ){
            continue;
          }
          
          // ...
        }


        // 💡 you can fail-exit the application like this
        if('something\'s wrong') {
          return $this->endWithError();
        }


        // 💡 you can access your own option(s) like this
        $myCustomAdditionalOpt = $this->getCliOptionstatic::CLI_OPT_....);
        if($myCustomAdditionalOpt) {
          // ...
        }


        // 💡 the last op must be this
        return $this->endWithSuccess();
    }
}

````


## 🧪 Test it

````bash
git clone git@github.com:TurboLabIt/php-symfony-basecommand.git
cd php-symfony-basecommand
clear && bash scripts/test-runner.sh

````
