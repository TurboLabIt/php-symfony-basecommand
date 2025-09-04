<?php
namespace TurboLabIt\BaseCommand\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TurboLabIt\BaseCommand\Service\DateMagician;


trait SpreadsheetTrait
{
    protected function saveDataAsSpreadsheet(array $arrData, string $sheetName = 'Data', ?array $arrDataFormat = [], ?string $varFilePath = 'report/data.xlsx') : string
    {
        $spreadsheet = $this->buildSpreadsheet($arrData, $sheetName, $arrDataFormat);
        return $this->saveSpreadsheet($spreadsheet, $varFilePath);
    }


    protected function buildSpreadsheet(array $arrData, string $sheetName = 'Data', ?array $arrDataFormat = []) : Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);
        $sheet->fromArray($arrData);

        // autosize
        foreach($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        // secondary sheet with meta-data
        $arrMetaData = [
            [ "Gen. date", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( (new \DateTime()) ) ],
            [ "Hostname", gethostname() ],
            [ "Env", $this->getEnv() ]
        ];

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Metadata');
        $sheet->fromArray($arrMetaData);
        $sheet->getStyle("B1")->getNumberFormat()->setFormatCode(DateMagician::INTL_FORMAT_YEAR_MONTH_TIME);

        // auto size metadata
        foreach($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        // show the report sheet by default
        $spreadsheet->setActiveSheetIndex(0);

        if( !empty($varFilePath) ) {
            return $this->saveSpreadsheet($spreadsheet, $varFilePath);
        }

        return $spreadsheet;
    }


    protected function saveSpreadsheetToFile(Spreadsheet $spreadsheet, string $varFilePath = 'report/data.xlsx') : string
    {
        $writer = new Xlsx($spreadsheet);
        $filePath = $this->projectDir->createVarDirFromFilePath($varFilePath);
        $writer->save($filePath);

        return $filePath;
    }
}