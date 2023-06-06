<?php
namespace TurboLabIt\BaseCommand\Traits;

use TurboLabIt\BaseCommand\Service\ProjectDir;


trait ProjectDirDirectTrait
{
    protected ?ProjectDir $projectDir;


    public function getProjectDir(array|string $subpath = '') : string
    {
        return $this->projectDir->getProjectDir($subpath);
    }


    public function createVarDir(array|string $subpath = '') : string
    {
        return $this->projectDir->getProjectDir($subpath);
    }
}
