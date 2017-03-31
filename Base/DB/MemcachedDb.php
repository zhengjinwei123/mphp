<?php

namespace MPHP\Base\DB;

class MemcachedDb
{

    protected $host;
    protected $port;
    protected $cache;

    public function __construct( $config = array('host' => '127.0.0.1', 'port' => 11211) )
    {
        $this->host = empty($config['host']) ? '127.0.0.1' : $config['host'];
        $this->port = empty($config['port']) ? 11211 : $config['port'];

        $this->connect();
    }

    public function connect()
    {
        $this->cache = new \Memcached();
        $this->cache->addserver($this->host, $this->port);
        $this->cache->setOption(\Memcached::OPT_COMPRESSION, true);
        $this->cache->setOption(\Memcached::OPT_TCP_NODELAY, true);
        //$this->cache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
        $this->cache->setOption(\Memcached::OPT_NO_BLOCK, false);
        $this->cache->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 5000);
        $this->cache->setOption(\Memcached::OPT_RETRY_TIMEOUT, 3000);
        $this->cache->setOption(\Memcached::OPT_SEND_TIMEOUT, 3000);
        $this->cache->setOption(\Memcached::OPT_RECV_TIMEOUT, 3000);
        $this->cache->setOption(\Memcached::OPT_POLL_TIMEOUT, 3000);

        return $this->cache;
    }

    /**
     * 设置值
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param int $expire 时间
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->cache->set( $key , $value , $expire);
    }

    public function add($key, $value, $expire = 0)
    {
        return $this->cache->add( $key , $value , $expire);
    }

    /**
     * 通过KEY获取数据
     * @param string $key KEY名称
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * 删除一条数据
     * @param string $key KEY名称
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function lock($key, $timeOut = 0)
    {
        return $this->add($key, 1, $timeOut);
    }

    public function unlock($key)
    {
        return $this->delete($key);
    }

    public function setMulti($items, $expire = 0)
    {
        return $this->cache->setMulti($items, $expire);
    }

    public function getMulti($keys)
    {
        return $this->cache->getMulti($keys);
    }

    public function deleteMulti($keys)
    {
        return $this->cache->deleteMulti($keys);
    }

    /**
     * 清空数据
     */
    public function flushAll()
    {
        return $this->cache->flushAll();
    }

    /**
     * 数据自增
     * @param string $key KEY名称
     */
    public function increment($key)
    {
        return $this->cache->incr($key);
    }

    /**
     * 数据自减
     * @param string $key KEY名称
     */
    public function decrement($key)
    {
        return $this->cache->decr($key);
    }

    public function getConn()
    {
        return $this->cache;
    }

    public function __destruct()
    {
        if ($this->cache) {
            $this->close();
            $this->cache = null;
        }
    }

    public function close()
    {
        if($this->cache) {
            $this->cache->quit();
        }
    }

    public function ping()
    {
        if($this->cache) {
            $ret = $this->cache->getVersion();
            if($ret) {
                return true;
            }
        }

        return false;
    }
}