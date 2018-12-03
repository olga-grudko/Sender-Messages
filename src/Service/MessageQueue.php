<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:29
 */

namespace App\Service;
use App\Redis\Redis;

/**
 * Класс для работы с очередями на редисе
 * Class MessageQueue
 *
 * @package App\Service
 */
class MessageQueue
{
    /** @var int Сколько элементов забирать из очереди  */
    const DEFAULT_COUNT = 2;
    /** @var Redis  */
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    /**
     * Добавляет элемент в конец очередь
     * @param array $messageData
     * @return $this
     */
    public function addToQueue(array $messageData) : self
    {
        $this->redis->push(json_encode($messageData));
        return $this;
    }

    /**
     * Забирает из начала очереди элемент и возвращает его
     * @return string
     */
    public function pop() : string
    {
        return $this->redis->lPop();
    }

    /**
     * Забирает указанное кол-во элементов из начала очередии возвращает их
     * @param int $count
     * @return array
     */
    public function popCount(int $count) : array
    {
        $messages = $this->redis->popCount($count);
        foreach($messages as &$oneMessage){
            $oneMessage = json_decode($oneMessage, true);
        }
        return $messages;
    }
}