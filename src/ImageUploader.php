<?php

namespace lera\test_image_upload;

use lera\test_image_upload\classes\ImageUrlUploader;
use lera\test_image_upload\classes\PathStructure;
use lera\test_image_upload\classes\CurlConnection;

class ImageUploader
{
    protected $uploader;
    protected $connectionConfig = [];
    protected $urlConfig = [];

    /**
     * Create a new ImageUploader Instance
     * @param string $dir_path path to upload directory
     * @param array $urls array with urls on images
     */
    public function __construct(?string $dir_path = null, ?array $urls = [])
    {
        $this->uploader = new ImageUrlUploader(new CurlConnection($this->connectionConfig), new PathStructure(), $dir_path, $urls, $this->urlConfig);
    }

    /**
     * @throws \Exception
     * @return ImageUrlUploader
     */
    public function getUploader(): ImageUrlUploader
    {
        return $this->uploader;
    }

    /**
     * @param array $config
     * @return Void
     */
    public function setConnectionConfig(array $config = []): Void
    {
        $this->connectionConfig = array_merge($this->connectionConfig, $config);
    }

    /**
     * @param array $config
     * @return Void
     */
    public function setUrlConfig(array $config = []): Void
    {
        $this->urlConfig = array_merge($this->urlConfig, $config);
    }

}
