<?php

namespace lera\images_uploader\interfaces;

interface ConnectionInterface
{
    public function getConnection(?string $url = null);
    public function is_url_exist($url);
}