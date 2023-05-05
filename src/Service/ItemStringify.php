<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;


class ItemStringify
{
    protected SluggerInterface $slugger;


    public function __construct(?SluggerInterface $slugger = null)
    {
        $this->slugger  = $slugger ?? (new AsciiSlugger());
    }


    public function buildItemName($item) : string
    {
        $txtName = '';

        if( is_object($item) ) {

            foreach(['getName', 'getTitle'] as $method) {
                if( method_exists($item, $method) ) {
                    $txtName = $item->$method();
                    break;
                }
            }

        } elseif( is_array($item) ) {

            foreach(['name', 'Name', 'title', 'Title'] as $key) {
                if( !empty($item[$key]) ) {
                    $txtName = $item[$key];
                    break;
                }
            }

        } else {

            $txtName = $item;
        }

        return trim($txtName);
    }


    public function buildItemTitle($item, $key = null) : string
    {
        $txtTitle = '';

        if( !empty($key) ) {
            $txtTitle = "[$key] ";
        }

        $txtTitle .= $this->buildItemName($item);

        return trim($txtTitle);
    }


    public function buildItemPath(
        $item, $key = null, string|array $preFolders = [], string|array $folders = [],
        string $separator = "/", bool $endWithSeparator = true
    ) : string
    {
        $arrPreFolders = empty($preFolders) ? [] : $this->buildFolderPathArray($preFolders);

        if( is_string($folders) ) {
            $folders = [$folders];
        }

        $itemTitle  = $this->buildItemTitle($item, $key);
        $folders    = array_merge($arrPreFolders, [$itemTitle], array_values($folders));

        return $this->buildFolderPath($folders, $separator, $endWithSeparator);
    }


    public function buildFolderPathArray(string|array $folders) : array
    {
        if( empty($folders) ) {
            throw new \InvalidArgumentException("Error building path: no folders provided");
        }

        if( is_string($folders) ) {
            $folders = [$folders];
        }

        $arrPath = [];
        foreach($folders as &$txtSubfolder) {

            $folder = $this->removeDirectorySeparator($txtSubfolder);

            if( !empty($folder) ) {
                $arrPath[] = $folder;
            }
        }

        return $arrPath;
    }


    public function buildFolderPath(string|array $folders, ?string $separator = null, bool $endWithSeparator = true) : string
    {
        $arrFolders = $this->buildFolderPathArray($folders);
        $separator  = $separator ?? DIRECTORY_SEPARATOR;
        $path       = implode($separator, $arrFolders);
        $path       .= $endWithSeparator ? $separator : '';

        return $path;
    }


    public function removeDirectorySeparator(?string $text, string $replaceWith = '-') : ?string
    {
        if( $text === null ) {
            return null;
        }

        $text = str_ireplace( ["/", "\\"], $replaceWith, $text);

        return trim($text);
    }


    public function slugify($item) : string
    {
        $text   = $this->buildItemName($item);
        $slug   = $this->slugger->slug($text);
        return $text;
    }
}
