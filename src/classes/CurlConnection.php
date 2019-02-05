<?php

namespace lera\images_uploader\classes;

use lera\images_uploader\interfaces\ConnectionInterface;

class CurlConnection implements ConnectionInterface
{

    public $errors = [];
    public $headers = [];
    protected $config = [
        'allow_redirects' => false,
        'proxy' => [
            'url' => null,
            'port' => null
        ],
        'recallsCount' => 0,
        'useHeaders' => false,
        'checkUrl' => true,
        'recallOn404' => false
    ];

    public function __construct($config)
    {
        $this->setConfig($config);
        $this->headers = $this->getHeaders();
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
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];
        $headers[] = 'X-Apple-Tz: 0';
        $headers[] = 'X-Apple-Store-Front: 143444,12';
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Accept-Language: en-US,en;q=0.5';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        $headers[] = 'Host: www.example.com';
        $headers[] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3';
        $headers[] = 'X-MicrosoftAjax: Delta=true';
        $headers[] = 'Access-Control: *';
        $headers[] = 'Access-Control-Allow-Origin: *';
        return $headers;
    }

    /**
     * @param bool|string $url
     * @param integer $recallsCount
     * @return array
     */
    public function getConnection(?string $url = null, ?int $recallsCount = null): array
    {
        if(!$url){
            $this->errors[$url]['curl']['text'] = 'No url set!';
            return ['url' => $url, 'content' => null, 'error' => $this->errors[$url]['curl']['text']];
        }
        if($this->config['checkUrl'] && !$this->is_url_exist($url)){
            return ['url' => $url, 'content' => null, 'error' => $this->errors[$url]['curl']['code']];
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, $this->config['useHeaders']?$this->headers:true);
        if(!empty($this->config['proxy']['url']) && !empty($this->config['proxy']['port'])){
            curl_setopt($ch, CURLOPT_PROXY, $this->config['proxy']['url']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->config['proxy']['port']);
        }
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $content = null;
        if($result = curl_exec($ch)){
            //detect redirect
            if(curl_getinfo($ch)['url'] != $url && !$this->config['allow_redirects']){
                $this->errors[$url]['curl']['text'] = 'Redirect detected!';
            }
            //get response code
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if((int)$code == 200 && empty($this->errors[$url]['curl'])){
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $content = substr($result, $header_size);
            }
            else{
                $this->errors[$url]['curl']['code'] = $code;
            }
        }
        if($error = curl_error($ch)){
            $this->errors[$url]['curl']['curl_text'] = $error;
        }
        curl_close($ch);
        //recalls
        if($recallsCount !== null){
            $recallsCount = $this->config['recallsCount'];
        }
        if(!empty($this->errors[$url]['curl']) && $recallsCount){
            if($this->config['recallOn404'] || !$this->config['recallOn404'] && isset($this->errors[$url]['curl']['code']) && (int)$this->errors[$url]['curl']['code'] != 404){
                for($i=0; $i < $recallsCount; $i++){
                    $this->errors[$url]['curl'] = [];
                    $repeatedRequest = $this->getConnection($url, 0);
                    if(empty($repeatedRequest['error'])){
                        return $repeatedRequest;
                    }
                }
            }
        }
        return ['url' => $url, 'content' => $content, 'error' => $this->errors[$url]['curl']??null];
    }

    /**
     * @param bool|string $url
     * @return bool
     */
    public function is_url_exist($url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($code == 200){
            $status = true;
        }else{
            $this->errors[$url]['curl']['code'] = $code;
            $status = false;
        }
        curl_close($ch);
        return $status;
    }
}