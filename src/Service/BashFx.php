<?php
namespace TurboLabIt\BaseCommand\Service;

use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TurboLabIt\BaseCommand\Command\AbstractBaseCommand;


class BashFx
{
    const ORDER_BY_NAME     = "order-by-name";
    const ORDER_BY_MOD_DATE = "order-by-mod-date";

    protected InputInterface $input;
    protected OutputInterface $output;
    protected ?SymfonyStyle $io;
    protected \DateTime $startedAt;


    public function setIo(InputInterface $input, OutputInterface $output) : SymfonyStyle
    {
        $this->input    = $input;
        $this->output   = $output;
        $this->io       = new SymfonyStyle($input, $output);

        return $this->io;
    }


    public function fxHeader(string $message, ?string $env = null) : self
    {
        $this->startedAt = new \DateTime();

        $message =
            trim($message) . PHP_EOL .
            "📅 " . $this->startedAt->format("H:i:s | l, F d, Y");

        if( !empty($env) ) {
            $message .= PHP_EOL .
                "🌳 $env";
        }

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


    public function fxEndFooter(int $result, ?string $commandName = null, ?string $txtFinalMessage = null) : int
    {
        $endAt      = new \DateTime();
        $timeTook   = $endAt->getTimestamp() - $this->startedAt->getTimestamp();
        $timeTook   = $timeTook / 60; // in minutes
        $timeTook   = round($timeTook, 2);

        if( $result == AbstractBaseCommand::SUCCESS ) {

            $bgColor    = 'bright-green';
            $word       = 'OK';

        } elseif( $result == AbstractBaseCommand::WARNING ) {

            $bgColor   = 'yellow';
            $word      = 'WARN';
            $result    = AbstractBaseCommand::SUCCESS;

        } else {

            $bgColor    = 'red';
            $word       = 'KO';
        }

        $txtFinalMessage   = empty($txtFinalMessage) ? '' : ($txtFinalMessage . PHP_EOL);
        $commandNameTxt    = empty($commandName) ? '' : "$commandName: ";

        $message    =
            $txtFinalMessage .
            "🏁 {$commandNameTxt}The End 🏁 | {$word}" . PHP_EOL .
            "📅 " . $endAt->format("H:i:s | l, F d, Y") . PHP_EOL .
            "⌚ Total time: " . $timeTook . " min.";

        $this->io->block($message, null, 'fg=black;bg=' . $bgColor, ' ', true);
        return $result;
    }


    public function fxListFiles(string $path, string $orderBy = self::ORDER_BY_NAME) : static
    {
        $dir = new \DirectoryIterator($path);
        $arrTableContent = [];

        /** @var \DirectoryIterator $fileinfo */
        foreach($dir as $fileinfo) {

            if ($fileinfo->isDot()) {
                continue;
            }

            $modTimestamp   = $fileinfo->getMTime();
            $compressedIcon =
                in_array($fileinfo->getExtension(), ["gz", "zip", "gzip", "rar"]) ? "🗜" : "🐡";

            $arrTableContent[] = [
                "Filename"      => $fileinfo->getFilename(),
                "Date"          => (new \DateTime())
                                    ->setTimestamp($modTimestamp)
                                    ->format('Y-m-d H:i:s'),
                "Compr."        => $compressedIcon,
                "Timestamp"     => $modTimestamp
            ];
        }

        // TODO implement others order by (based on $orderBy)

        usort($arrTableContent, function(array $item1, array $item2) {
            return strcmp( mb_strtolower($item1["Filename"]), mb_strtolower($item2["Filename"]));
        });

        foreach($arrTableContent as &$arrItem) {
            unset($arrItem["Timestamp"]);
        }

        (new Table($this->output))
            ->setHeaders( array_keys($arrTableContent[0]) )
            ->setRows($arrTableContent)
            ->render();

        return $this;
    }
}
