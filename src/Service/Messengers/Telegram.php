<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:14
 */

namespace App\Service\Messengers;


use App\Service\Message;
use App\Service\MessengerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Отправка сообщений через Telegram
 * Class Telegram
 * @package App\Service\Messengers
 */
class Telegram extends Message implements MessengerInterface
{
    /**
     * Telegram constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
    }

    /**
     * @return Telegram
     */
    public function sendMessage() : self
    {
        //используя $this->messageData отправить сообщение
        echo 'Отправка сообщения через ' . self::class . PHP_EOL;
        return $this;
    }
}