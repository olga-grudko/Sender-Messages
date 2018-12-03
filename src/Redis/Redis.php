<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:37
 */

namespace App\Redis;


use Predis\Client;

/**
 * Класс-обертка для работы с редисом
 * Class Redis
 * @package App\Redis
 */
class Redis
{
    private $host = '192.168.1.7';
    private $port = '6379';
    private $database = 7;

    private $redis;
    private $messageQueueKey = 'messagesToSend';

    const MAX_COUNT = 20;

    public function __construct()
    {
        $this->redis = new Client([
        'host' => $this->host,
        'port' => $this->port,
        'database' => $this->database,
        ]);
    }

    /**
     * Добавляет элемент в конец списка
     * @param string $value
     */
    public function push(string $value) : void
    {
        $this->redis->rpush($this->messageQueueKey, $value);
    }

    /**
     * Возвращает первый элемент из списка и убирает его из списка
     * @return string
     */
    public function lPop() : string
    {
        $value =  $this->redis->lpop($this->messageQueueKey);
        return $value;
    }


    /**
     * Возвращает указанное кол-во элементов и убирает их из начала списка
     * @param int $count
     *
     * @return array
     */
    public function popCount(int $count = self::MAX_COUNT) : array
    {
        if ($count > self::MAX_COUNT || $count <= 0) {
            $count = self::MAX_COUNT;
        }

        $length = $this->redis->llen($this->messageQueueKey);
        if ($length > $count) {
            $length = $count;
        }
        $data = [];
        for($i = 0; $i< $length; $i++) {
            $data[] = $this->lPop();
        }

        return $data;
    }


    /**
     * Удаляет из начала списка один эелемент
     */
    public function del()
    {
        $this->redis->rpop($this->messageQueueKey);
    }

}