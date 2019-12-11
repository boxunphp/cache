<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/10
 * Time: 2:24 PM
 */

namespace All\Cache;

use Ali\InstanceTrait;
use All\Cache\Drivers\ApcuCache;
use All\Cache\Drivers\FileCache;
use All\Cache\Drivers\MemcachedCache;
use All\Cache\Drivers\RedisCache;
use All\Config\Config;

abstract class CacheAbstract
{
    use InstanceTrait;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * 缓存类型
     * @var int
     */
    protected $cacheType = Cache::TYPE_MEMCACHED;
    /**
     * 配置文件路径, 配合Config使用
     * @var string
     */
    protected $cacheConfigPath = '';
    /**
     * 配置文件的key
     * @var string
     */
    protected $cacheConfigKey = '';
    /**
     * 配置信息,如果有定义了path和key,会被覆盖
     * @var array|null
     */
    protected $cacheConfig = [];
    /**
     * 缓存前缀
     * @var string
     */
    protected $cachePrefixKey = '';
    protected $cacheTTL = 0;

    /**
     * CacheAbstract constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if ($this->cacheConfigPath && $this->cacheConfigKey) {
            $Config = Config::getInstance()->setPath($this->cacheConfigPath);
            $this->cacheConfig = $Config->get($this->cacheConfigKey);
        }

        switch ($this->cacheType) {
            case Cache::TYPE_MEMCACHED:
                $this->cache = MemcachedCache::getInstance($this->cacheConfig);
                break;
            case Cache::TYPE_REDIS:
                $this->cache = RedisCache::getInstance($this->cacheConfig);
                break;
            case Cache::TYPE_APCU:
                $this->cache = ApcuCache::getInstance($this->cacheConfig);
                break;
            case Cache::TYPE_FILE:
                $this->cache = FileCache::getInstance($this->cacheConfig);
                break;
            default:
                $this->cache = MemcachedCache::getInstance($this->cacheConfig);
                break;
        }
    }

    public function set($key, $value, $expiration = 0)
    {
        $expiration = $expiration ?: ($this->cacheTTL ?: 0);
        return $this->cache->set($this->cachePrefixKey . $key, $value, $expiration);
    }

    public function get($key)
    {
        return $this->cache->get($this->cachePrefixKey . $key);
    }

    public function delete($key)
    {
        return $this->cache->delete($this->cachePrefixKey . $key);
    }

    public function setMulti(array $items, $expiration = 0)
    {
        $expiration = $expiration ?: ($this->cacheTTL ?: 0);
        $newItems = [];
        foreach ($items as $key => $value) {
            $newItems[$this->cachePrefixKey . $key] = $value;
        }
        return $this->cache->setMulti($newItems, $expiration);
    }

    public function getMulti(array $keys)
    {
        $newKeys = [];
        foreach ($keys as $idx => $key) {
            $newKeys[$idx] = $this->cachePrefixKey . $key;
        }
        $result = $this->cache->getMulti($newKeys);
        $data = [];
        foreach ($newKeys as $idx => $key) {
            if (isset($result[$key])) {
                $data[$keys[$idx]] = $result[$key];
            }
        }
        return $data;
    }

    public function deleteMulti(array $keys)
    {
        $newKeys = [];
        foreach ($keys as $key) {
            $newKeys[] = $this->cachePrefixKey . $key;
        }
        return $this->cache->deleteMulti($newKeys);
    }
}