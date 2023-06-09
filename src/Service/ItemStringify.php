<?php
namespace TurboLabIt\BaseCommand\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;


class ItemStringify
{
    public function __construct(protected ?SluggerInterface $slugger = null)
    {
        $this->slugger = $slugger ?? (new AsciiSlugger());
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


    public function slugify($item, ?string $separator = null, bool $endWithSeparator = false) : string
    {
        $text   = $this->buildItemName($item);
        $slug   = $this->slugger->slug($text)->toString();
        $slug   = mb_strtolower($slug);

        if($endWithSeparator) {

            $separator  = $separator ?? DIRECTORY_SEPARATOR;
            $slug .= $separator;
        }

        return $slug;
    }


    public function slugifyMultipleAsArray(iterable $arrItems, bool $reverse = false) : array
    {
        $arrSlugs = [];
        foreach($arrItems as $item) {
            $arrSlugs[] = $this->slugify($item);
        }

        if($reverse) {
            $arrSlugs = array_reverse($arrSlugs, true);
        }

        return $arrSlugs;
    }


    public function slugifyMultipleAsPath(iterable $arrItems, ?string $separator = null, bool $endWithSeparator = true, bool $reverse = false) : string
    {
        $arrSlugs   = $this->slugifyMultipleAsArray($arrItems, $reverse);
        $separator  = $separator ?? DIRECTORY_SEPARATOR;
        $slugs      = implode($separator, $arrSlugs);

        return $slugs;
    }

}
