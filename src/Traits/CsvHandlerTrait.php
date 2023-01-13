<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;

use Symfony\Component\Console\Helper\ProgressBar;


trait CsvHandlerTrait
{
    protected int $lastCsvReadRowsNum = 0;
    
    
    protected function processCsv(string $csvFilePath, callable $fxProcess, string $delimiter = ',', int $headerOffset = 0) : self
    {
        $oCsvData = $this->readCsv($csvFilePath, $delimiter, $headerOffset);
        
        $this->fxInfo("This may take a while...");
        $this->io->newLine();
        
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
        }
        
        $csvFile = \League\Csv\Reader::createFromPath($csvFilePath);
        $csvFile->setDelimiter($delimiter);
        $csvFile->setHeaderOffset($headerOffset);
        
        $this->lastCsvReadRowsNum = count($csvFile);
        
        $oCsvData = $csvFile->getRecords();
        
        if( !$silent ) {
            $this->fxOK("🔢 CSV ready. ##" . number_format($this->lastCsvReadRowsNum, 0, ',', '.') . "## row(s) returned");
        }
        
        return $oCsvData;
    }
}
