<?php

namespace lera\images_uploader\interfaces;


interface PathStructureInterface
{
    public function setDirPath(string $dir_path);
    public function createDir(string $path);
}