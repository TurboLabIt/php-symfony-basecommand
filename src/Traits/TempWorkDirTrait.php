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
    
    
    protected function clearWorkingDir() : string
    {
        $path = $this->getTempWorkingDirPath();
        $this->deleteDirectory($path);
        return $this->getTempWorkingDirPath();
    }
    
    
    protected function deleteDirectory($dir) : bool
    {
        if ( !file_exists($dir) ) {
            return true;
        }
    
        if ( !is_dir($dir) ) {
            return unlink($dir);
        }
    
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
    
            if ( !$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item) ) {
                return false;
            }
        }
    
        return rmdir($dir);
    }
}
