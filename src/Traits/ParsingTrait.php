<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;


trait ParsingTrait
{
    protected function selectItemsFromCsv(?string $csvIds, ?array $arrDataSource, string $itemTitle, string $relatedItemsName) : ?array
    {
        if( empty($csvIds) ) {
            return null;
        }

        $arrFids = explode(',', $csvIds);
        array_walk_recursive($arrFids, function(&$value) {
            if( !is_null($value) ) {
                $value = (int)trim($value);
            }
        });

        $arrItems   = [];
        $arrFailIds = [];
        foreach($arrFids as $fId) {

            if( array_key_exists($fId, $arrDataSource) ) {
                $arrItems[$fId] = $arrDataSource[$fId];
            } else {
                $arrFailIds[] = $fId;
            }
        }

        if( !empty($arrFailIds) ) {

            $this->io->newLine();
            $this->fxWarning(
                "$itemTitle: Some related $relatedItemsName don't exist. " .
                "Failing item(s): " . implode(',', $arrFailIds) . PHP_EOL
            );
        }

        return $arrItems;
    }
}
