<?php

namespace lera\images_uploader\classes;

use lera\images_uploader\interfaces\ConnectionInterface;
use lera\images_uploader\interfaces\PathStructureInterface;

class ImageUrlUploader
{

    protected $urls = [];
    protected $connection;
    protected $paths;
    protected $config = [
        'url_rules' => '/(?i)\.(jpg|png|gif)$/',
        'allowed_mime' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ]
    ];
    protected $errors = [];

    /**
     * Create a new ImageUploader Instance
     * @param ConnectionInterface $connection url connection
     * @param PathStructureInterface $paths manage directories
     * @param string $dir_path path to upload directory
     * @param array $urls array with urls on images
     * @param array $config
     */
    public function __construct(ConnectionInterface $connection, PathStructureInterface $paths, ?string $dir_path = null, ?array $urls = [], array $config = [])
    {
        $this->connection = $connection;
        $this->paths = $paths;
        $this->setConfig($config);

        if($dir_path !== null){
            $this->paths->setDirPath($dir_path);
        }
        if($urls){
            $this->addUrls($urls);
        }
    }

    /**
     * @param string $dir_path
     * @return string
     */
    public function setDirPath(string $dir_path): string
    {
        $this->paths->setDirPath($dir_path);
        return $this->getDirPath();
    }

    /**
     * @return string
     */
    public function getDirPath(): string
    {
        return $this->paths->getDirPath();
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    public function removeImage(string $imagePath): bool
    {
        return $this->paths->removeImage($imagePath);
    }

    /**
     * @param string $path
     * @param null|string $root
     * @return
     */
    public function createDir(string $path, ?string $root = null)
    {
        return $this->paths->createDir($path, $root);
    }

    /**
     * @param string $url url on image
     * @return Void
     * @throws \Exception
     */
    public function addUrl(string $url): Void
    {
        try {
            $this->validateUrl($url);
            $this->urls[] = $url;
        }
        catch (\Exception $e){
            $this->errors[] = [
                'url' => $url,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param array $urls array with images urls
     * @return Void
     * @throws \Exception
     */
    public function addUrls(array $urls): Void
    {
        foreach ($urls as $url){
            $this->addUrl($url);
        }
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array of images urls
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param string $url url on image
     * @return bool
     */
    public function removeUrl(string $url): bool
    {
        if($key = array_search($url, $this->urls)){
            unset($this->urls[$key]);
            array_filter($this->urls);
            return true;
        }
        return false;
    }

    /**
     * @param array $urls array of images urls
     * @return array $failedUrls list of not existed urls in $this->urls
     */
    public function removeUrls(array $urls): array
    {
        $failedUrls = [];
        foreach ($urls as $url){
            if(!$this->removeUrl($url)){
                $failedUrls[] = $url;
            }
        }
        return $failedUrls;
    }

    /**
     * @param string $url url on image
     * @return Void
     * @throws \Exception
     */
    public function validateUrl(string $url): Void
    {
        if(!preg_match($this->config['url_rules'], $url)){
            throw new \Exception('Wrong image extension!');
        }
    }

    public function saveImages(): array
    {
        $this->removeUrlDuplicates();
        $result = [];
        foreach ($this->urls as $url){
            $response = $this->getImageByUrl($url);
            if(!empty($response['content'])){
                if($mime = $this->validateImage($response['content'])){
                    if(isset($this->config['allowed_mime'][$mime]) && $fileName = $this->getFileName($url)){
                        $result[$url] = $this->paths->saveFile($this->getDirPath().DIRECTORY_SEPARATOR.$fileName, $response['content']);
                    }
                }
                else{
                    $result[$url]['errors'] = 'Not valid image file!';
                }
            }
            else{
                $result[$url]['errors'] = $response['error'];
            }
        }
        return $result;
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function getFileName(string $url)
    {
        if($info = pathinfo($url)){
            return $info['basename']??false;
        }
        return false;
    }

    /**
     * @param $image
     * @return string
     */
    public function validateImage($image): string
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $fileInfo->buffer($image);
        return $mime;
    }

    /**
     * @return void
     */
    public function removeUrlDuplicates(): Void
    {
        $this->urls = array_unique($this->urls);
    }

    /**
     * @param array $config
     * @return Void
     */
    protected function setConfig(array $config = []): Void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string $url
     * @return array
     */
    protected function getImageByUrl(string $url): array
    {
        return $this->connection->getConnection($url);
    }

}