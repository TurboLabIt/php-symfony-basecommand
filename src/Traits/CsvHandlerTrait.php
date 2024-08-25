<?php
namespace TurboLabIt\BaseCommand\Traits;

use Symfony\Component\Console\Helper\ProgressBar;


trait CsvHandlerTrait
{
    protected int $lastCsvReadRowsNum = 0;


    protected function processCsv(string $csvFilePath, callable $fxProcess, string $delimiter = ',', int $headerOffset = 0) : static
    {
        $oCsvData = $this->readCsv($csvFilePath, false, $delimiter, $headerOffset);

        $progressBar = new ProgressBar($this->output, $this->lastCsvReadRowsNum);
        $progressBar->start();

        foreach($oCsvData as $arrRow) {

            $fxProcess($arrRow);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine(2);

        return $this;
    }


    protected function readCsv(string $csvFilePath, bool $silent = false, string $delimiter = ',', int $headerOffset = 0) : \League\Csv\MapIterator
    {
        if( !$silent ) {
            $this->fxInfo("📑 Accessing CSV ##" . $csvFilePath . "##");
            $this->fxInfo("This may take a while...");
        }

        $csvFile = \League\Csv\Reader::createFromPath($csvFilePath);
        $csvFile->setDelimiter($delimiter);
        $csvFile->setHeaderOffset($headerOffset);

        $this->lastCsvReadRowsNum = count($csvFile);

        $oCsvData = $csvFile->getRecords();

        if( !$silent ) {
            $this->fxOK("🔢 CSV ready. ##" . number_format($this->lastCsvReadRowsNum, 0, ',', '.') . "## row(s) returned");
            $this->io->newLine();
        }

        return $oCsvData;
    }


    protected function writeCsv(string $csvFilePath, array $arrDataToWrite, bool $silent = false, string $delimiter = ',') : static
    {
        if( !$silent ) {
            $this->fxInfo("📑 Writing CSV ##" . $csvFilePath . "##");
        }

        $csv = \League\Csv\Writer::createFromPath($csvFilePath, 'w+');
        $csv->setDelimiter($delimiter);
        $csv->insertAll($arrDataToWrite);

        return $this;
    }


    protected function writeCsvToVarPath(array|string $relativeFilePath, array $arrDataToWrite, bool $silent = false, string $delimiter = ',') : static
    {
        $filePath = $this->projectDir->createVarDirFromFilePath($relativeFilePath);
        return $this->writeCsv($filePath, $arrDataToWrite, $silent, $delimiter);
    }
}
