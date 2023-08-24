<?php
namespace TurboLabIt\BaseCommand\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;


class ProjectDir
{
    public function __construct(protected ContainerBagInterface $parameterBag)
    {}


    public function getProjectDir(array|string $subpath = '') : string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir') . DIRECTORY_SEPARATOR;

        if( empty($subpath) ) {
            return $projectDir;
        }

        if( is_string($subpath) ) {

            $projectDir .= $subpath;

        } elseif( is_array($subpath) ) {

            $projectDir .= implode(DIRECTORY_SEPARATOR, $subpath);
        }

        $projectDir = trim($projectDir);

        // adding trailing slash
        $projectDir = rtrim($projectDir, '\\/') . DIRECTORY_SEPARATOR;

        return $projectDir;
    }


    public function getProjectDirFromFile(array|string $filePath) : string
    {
        if( is_string($filePath) ) {
            $filePath = explode(DIRECTORY_SEPARATOR, $filePath);
        }

        $folders    = array_slice($filePath, 0, -1);
        $filename   = array_slice($filePath, -1);
        $filename   = reset($filename);

        $path = $this->getProjectDir($folders) . $filename;
        return $path;
    }
    

    public function getVarDir(array|string $subpath = '') : string
    {
        if( is_array($subpath) ) {
            $subpath = implode(DIRECTORY_SEPARATOR, $subpath);
        }

        $subpath = trim($subpath);

        if( substr($subpath, 0, strlen('var/')) == 'var/' ) {
            $subpath = substr($subpath, strlen('var/'));
        }

        if( substr($subpath, 0, strlen('var\\')) == 'var\\' ) {
            $subpath = substr($subpath, strlen('var\\'));
        }

        $subpath = 'var' . DIRECTORY_SEPARATOR . $subpath;

        $path = $this->getProjectDir($subpath);
        return $path;
    }


    public function getVarDirFromFilePath(array|string $filePath) : string
    {
        if( is_string($filePath) ) {
            $filePath = explode(DIRECTORY_SEPARATOR, $filePath);
        }

        $folders    = array_slice($filePath, 0, -1);
        $filename   = array_slice($filePath, -1);
        $filename   = reset($filename);

        $path = $this->getVarDir($folders) . $filename;
        return $path;
    }


    public function createVarDir(array|string $subpath = '') : string
    {
        $path = $this->getVarDir($subpath);

        if( !is_dir($path) ) {
            mkdir($path, 0777, true);
        }

        return $path;
    }


    public function createVarDirFromFilePath(array|string $filePath) : string
    {
        $dirPath = dirname($filePath);
        $this->createVarDir($dirPath);

        $absoluteFilePath = $this->getVarDirFromFilePath($filePath);

        return $absoluteFilePath;
    }
}
