<?php

namespace lera\test_image_upload\interfaces;


interface PathStructureInterface
{
    public function setDirPath(string $dir_path);
    public function createDir(string $path);
}