<?php

class Cache
{

    private static ?Cache $instance = null;
    private \Predis\Client $client;

    private function __construct()
    {
        $this->client = new Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);
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
        $result = $this->client->set($key, json_encode($value));
        $this->client->expire($key, $expire);
        return $result;
    }

    public function get($key)
    {
        return json_decode($this->client->get($key), true);
    }

    public function del($key): int
    {
        return $this->client->del($key);
    }

    public function isKey($key): bool
    {
        if ($this->client->exists($key)) {
            return true;
        } else {
            return false;
        }
    }

    public function clearCache(): void
    {
        $this->client->flushall();
    }
}
