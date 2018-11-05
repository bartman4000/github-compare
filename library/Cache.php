<?php
/**
 * Created by PhpStorm.
 * User: Bartek
 * Date: 2017-05-26
 * Time: 19:36
 */

namespace GitHubCompare;

class Cache
{
    const HOUR = 3600;
    const DAY = 86400;

    protected $cacheDir;
    private $logger;

    public function __construct()
    {
        $this->cacheDir = realpath(dirname(__FILE__) . '/../cache');
        $root = realpath(dirname(__FILE__) . '/../');
        $this->logger = new \Monolog\Logger('Cache');
        $file_handler = new \Monolog\Handler\StreamHandler($root."/logs/app.log");
        $this->logger->pushHandler($file_handler);
    }

    /**
     * @param string $owner
     * @param string $repo
     * @return string
     */
    public function readCache($owner,$repo)
    {
        return file_get_contents($this->getCacheFilePath($owner,$repo));
    }

    /**
     * @param string $owner
     * @param string $repo
     * @param string $data
     */
    public function saveCache($owner,$repo,$data)
    {
        $path = $this->getCacheFilePath($owner,$repo);
        if($this->isFileWriteable($path))
        {
            file_put_contents($path,$data);
        }
    }

    /**
     * @param string $owner
     * @param string $repo
     * @return bool
     */
    public function isCache($owner,$repo)
    {
        $path = $this->getCacheFilePath($owner,$repo);
        if($this->isFileReadable($path))
        {
            $updateTimestamp = filemtime($path);
            if((time() - $updateTimestamp) < Cache::HOUR)
            {
                return true;
            }
        }
        return false; //no cache file or cache is over 1 hour old
    }

    /**
     * @param string $owner
     * @param string $repo
     * @return string
     */
    public function getCacheFilePath($owner,$repo)
    {
        return $this->cacheDir.'/'.$owner.'-'.$repo.'.json';
    }

    /**
     * @param string $file
     * @return bool
     */
    public function isFileReadable($file)
    {
        return file_exists($file) && ($handle = fopen($file, "r")) !== FALSE;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function isFileWriteable($file)
    {
        return $handle = fopen($file, "w") !== FALSE;
    }
}