<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;


trait CsvHandlerTrait
{
    protected int $lastCsvReadRowsNum = 0;
    
    
    protected function readCsv(string $csvFilePath, bool $silent = false, string $delimiter = ',', int $headerOffset = 0) : \League\Csv\MapIterator
    {
        if( !$silent ) {
            $this->fxInfo("📑 Accessing CSV ##" . $csvFilePath . "##");
        }
        
        $csvFile = \League\Csv\Reader\Reader::createFromPath($csvFilePath);
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
