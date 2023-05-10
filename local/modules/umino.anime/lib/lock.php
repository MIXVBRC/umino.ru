<?php


namespace Umino\Anime;


/**
 * Class Lock
 * @package Umino\Anime\Tools
 */
class Lock
{
    /** @var string */
    private static $dir = '/../lockfiles/';

    /** @var null */
    protected $key = null;

    /** @var bool|null|resource */
    protected $file = null;
    protected $fileName = null;

    /** @var bool */
    protected $own = false;

    /**
     * Lock constructor
     * @param $key
     */
    function __construct($key)
    {
        $this->key = $key;
        mkdir(__DIR__ . static::$dir, 0775, true);
        $this->fileName = __DIR__ . static::$dir . "$key.lockfile";
        $this->file = fopen($this->fileName, 'w+');
    }

    /**
     * Lock destructor
     */
    function __destruct()
    {
        if ($this->own == true) {
            $this->unlock();
        }
    }

    /**
     * @return bool
     */
    function unlock()
    {
        if ($this->own == true) {
            if (!flock($this->file, LOCK_UN)) {
                return false;
            }
            ftruncate($this->file, 0);
            fwrite($this->file, "Unlocked");
            fflush($this->file);
            $this->own = false;
        }

        return true;
    }

    /**
     * @return bool
     */
    function lock()
    {
        if (!flock($this->file, LOCK_EX | LOCK_NB)) {
            return false;
        }
        ftruncate($this->file, 0);
        fwrite($this->file, "Locked");
        fflush($this->file);
        $this->own = true;
        return true;
    }
}