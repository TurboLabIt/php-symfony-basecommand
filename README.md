# Symfony BaseCommand

An extensions of Symfony Console Command to build your own CLI commands even faster.

<p align="center">
  <img src="https://i.postimg.cc/FHBfDbJN/z-Shot-1673219261.png" />
</p>


## 📦 Install it with composer

````bash
symfony composer config repositories.TurboLabIt/BaseCommand git https://github.com/TurboLabIt/php-symfony-basecommand.git
symfony composer require turbolabit/php-symfony-basecommand:dev-main

````


## 🚀 A template for your own Command

```php
<?php declare(strict_types=1);
namespace App\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TurboLabIt\PhpSymfonyBasecommand\Command\AbstractBaseCommand;


#[AsCommand(name: 'MyCommand')]
class MyCommand extends AbstractBaseCommand
{
    // 💡 define your own specific --option(s)
    const CLI_OPT_MY_OPT = "my-opt"


    // 💡 set your `$allow` options: https://github.com/TurboLabIt/php-symfony-basecommand/blob/main/src/Traits/CliOptionsTrait.php
    protected bool $allow...
    
    
    public function __construct(array $arrConfig = [])
    {
        parent::__construct($arrConfig);
        // 💡 inject your own dependencies
    }


    protected function configure()
    {
        parent::configure();
        // 💡 add your own specific option(s)
        $this->addOption(static::CLI_OPT_MY_OPT, null, InputOption::VALUE_NONE, 'Text description')
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
        if('something's wrong') {
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
