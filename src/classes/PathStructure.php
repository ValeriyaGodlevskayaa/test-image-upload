<?php

namespace lera\images_uploader\classes;

use lera\images_uploader\interfaces\PathStructureInterface;

class PathStructure implements PathStructureInterface
{
    protected $dir_path;
    
    /**
     * @param string $dir_path path to upload directory
     * @return Void
     * @throws \Exception
     */
    public function setDirPath(string $dir_path): Void
    {
        if(!is_dir($dir_path)){
            throw new \Exception('Wrong uploads directory path!');
        }
        $this->dir_path = $dir_path;
    }

    /**
     *@return string
     */
    public function getDirPath(): string
    {
        return $this->dir_path;
    }

    /**
     * @param string $path path to upload directory
     * @param string $root path to root directory
     * @throws \Exception
     * @return string $path created directory full path
     */
    public function createDir(string $path, ?string $root = null): string
    {
        if($root === null && !isset($_SERVER['DOCUMENT_ROOT'])){
            throw new \Exception('Can not get root path!');
        }
        if(!is_dir($root)){
            throw new \Exception('Wrong root path!');
        }
        $path = $root.DIRECTORY_SEPARATOR.$path;
        if (!is_dir($path) && !mkdir( $path, 0777, true)) {
            throw new \Exception('Can not create directory!');
        }
        return $path;
    }

    /**
     * @param string $fileFullPath
     * @param string $fileContent
     * @return mixed
     */
    public function saveFile(string $fileFullPath, string $fileContent)
    {
        if(file_put_contents($fileFullPath, $fileContent)){
            return $fileFullPath;
        }
        return false;
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    public function removeImage(string $imagePath): bool
    {
        if(is_file($imagePath) && unlink($imagePath)){
            return true;
        }
        return false;
    }
    
}