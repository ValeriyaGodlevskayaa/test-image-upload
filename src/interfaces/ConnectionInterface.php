<?php

namespace lera\test_image_upload\interfaces;

interface ConnectionInterface
{
    public function getConnection(?string $url = null);
    public function is_url_exist($url);
}