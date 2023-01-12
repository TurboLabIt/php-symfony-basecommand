<?php
namespace TurboLabIt\PhpSymfonyBasecommand\Traits;


trait TempWorkDirTrait
{
    protected function getTempWorkingDirPath() : string
    {
        $path = sys_get_temp_dir();
        
        // make sure it has a trailing slash
        $path .= substr($path, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR;
      
        $path .= $this->getName() . DIRECTORY_SEPARATOR;
        
        if( !is_dir($path) ) {
            mkdir($path);
        }
        
        return $path;
    }
    
    
    protected function getTempWorkingDirFile(string $filename) : string
    {
        $path = $this->getTempWorkingDirPath() . $filename;
        return $path;
    }
}
