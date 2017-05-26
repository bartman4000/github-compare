<?php
/**
 * Created by PhpStorm.
 * User: Bartek
 * Date: 2017-05-26
 * Time: 19:00
 */

namespace SchibstedApp;

use GuzzleHttp;

class InternalApiClient
{
    private $logger;

    public function __construct()
    {
        $root = realpath(dirname(__FILE__) . '/../');
        $this->logger = new \Monolog\Logger('InternalApiClient');
        $file_handler = new \Monolog\Handler\StreamHandler($root."/logs/app.log");
        $this->logger->pushHandler($file_handler);
    }

    public function call($method,$resource,$options)
    {
        $Client = new GuzzleHttp\Client();
        $options['headers'] = [
            'Accept'     => 'application/json'
        ];

        $url = "http://".APP_HOST.'/api'.$resource;
        $query = '';
        $params = array();
        if(!empty($options['query']))
        {
            foreach($options['query'] as $key => $value)
            {
                $params[] = $key.'='.$value;
            }
            $query = "?".implode('&',$params);
        }

        $this->logger->addInfo("{$method} {$url}{$query}");

        try {
            $response = $Client->request($method, $url, $options);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->logger->addWarning("Called internal resource {$resource} with response ".($e->getMessage()));
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->logger->addInfo("Called internal resource {$resource} with response ".($response->getStatusCode()." ".$response->getReasonPhrase()));
        return $response->getBody()->getContents();
    }

    public function get($resource,$options)
    {
        return $this->call("GET", $resource,$options);
    }
}