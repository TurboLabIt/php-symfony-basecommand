<?php
namespace TurboLabIt\BaseCommand\Service;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use TurboLabIt\BaseCommand\Command\AbstractBaseCommand;


class BashFx
{
    protected ?SymfonyStyle $io;
    protected \DateTime $startedAt;


    public function setIo(InputInterface $input, OutputInterface $output) : SymfonyStyle
    {
        $this->io = new SymfonyStyle($input, $output);
        return $this->io;
    }


    public function fxHeader(string $message) : self
    {
        $this->startedAt = new \DateTime();

        $message =
            trim($message) . PHP_EOL .
            "📅 " . $this->startedAt->format("H:i:s | l, F d, Y");

        $this->io->block($message, null, 'fg=black;bg=cyan', ' ', true);
        return $this;
    }


    public function fxTitle(string $message) : self
    {
        $this->io->writeln('');

        $formattedMessage = '<bg=blue>' . $message . '</>';
        $this->io->writeln($formattedMessage);

        $underline = '<bg=blue>' . str_repeat('-', mb_strlen($message) ) . '</>';
        $this->io->writeln($underline);

        $this->io->writeln('');

        return $this;
    }


    public function fxInfo(string $message) : self
    {
        $formattedMessage = '<fg=bright-blue>ℹ ' . $message . '</>';
        $this->io->writeln($formattedMessage);
        return $this;
    }


    public function fxOK(?string $message = null) : self
    {
        if( empty($message) ) {
            $message = "OK";
        }

        $this->io->writeln("<info>$message</>");
        return $this;
    }


    public function fxWarning(string $message) : self
    {
        $this->io->note("$message");
        return $this;
    }


    public function fxError(string $message) : self
    {
        $this->io->block($message, null, 'fg=black;bg=red', ' ', true);
        return $this;
    }


    public function fxCatastrophicError(string $message, bool $endFooterAndStop = true, ?string $commandName = null) : int
    {
        $txtCatastrophicError = "🛑 Catastrophic error 🛑";
        $fullMessage = $txtCatastrophicError . PHP_EOL . $message;
        $this->fxError($fullMessage);

        if( $endFooterAndStop) {

            $this->fxEndFooter(AbstractBaseCommand::FAILURE, $commandName);
            $fullMessage = $txtCatastrophicError . " | " . $message;
            throw new RuntimeException($fullMessage);
        }

        return AbstractBaseCommand::FAILURE;
    }


    public function fxEndFooter(int $result, ?string $commandName = null) : int
    {
        $endAt      = new \DateTime();
        $timeTook   = $endAt->getTimestamp() - $this->startedAt->getTimestamp();
        $timeTook   = $timeTook / 60; // in minutes
        $timeTook   = round($timeTook, 2);

        if( $result == AbstractBaseCommand::SUCCESS ) {

            $bgColor    = 'bright-green';
            $word       = 'OK';

        } else {

            $bgColor    = 'red';
            $word       = 'KO';
        }

        $commandNameTxt = empty($commandName) ? '' : "$commandName: ";

        $message    =
            "🏁 {$commandNameTxt}The End 🏁 | " . $word . PHP_EOL .
            "📅 " . $endAt->format("H:i:s | l, F d, Y") . PHP_EOL .
            "⌚ Total time: " . $timeTook . " min.";

        $this->io->block($message, null, 'fg=black;bg=' . $bgColor, ' ', true);
        return $result;
    }
}
