<?php

class Cache
{

    private static ?Cache $instance = null;
    private \Predis\Client $client;
    private bool $cache;

    private function __construct($host = '127.0.0.1', $port = 6379)
    {
        if (extension_loaded('redis')) {
            $this->cache = true;
            $this->client = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $host,
                'port'   => $port,
            ]);
        } else {
            $this->cache = false;
        }
    }

    static public function getInstance(): Cache
    {
        if (static::$instance === null) {
            static::$instance = new Cache();
        }
        return static::$instance;
    }

    public function set($key, $value, $expire = 86400): string|\Predis\Response\Status
    {
        if (!$this->cache) {
            return '';
        }
        $result = $this->client->set($key, json_encode($value));
        $this->client->expire($key, $expire);
        return $result;
    }

    public function get($key)
    {
        if (!$this->cache) {
            return '';
        }
        return json_decode($this->client->get($key), true);
    }

    public function del($key): int
    {
        if (!$this->cache) {
            return 0;
        }
        return $this->client->del($key);
    }

    public function isKey($key): bool
    {
        if (!$this->cache) {
            return false;
        }
        if ($this->client->exists($key)) {
            return true;
        } else {
            return false;
        }
    }

    public function clearCache(): void
    {
        if ($this->cache) {
            $this->client->flushall();
        }
    }
}
