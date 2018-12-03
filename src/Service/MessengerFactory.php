<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:11
 */

namespace App\Service;


use App\Service\Messengers\Telegram;
use App\Service\Messengers\Viber;
use Doctrine\ORM\EntityManager;

/**
 * Фабрика создания классов мессенджеров
 * Class MessengerFactory
 *
 * @package App\Service
 */
class MessengerFactory
{
    public $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /** Маппинг классов */
    private const MESSENGER_MAPPING = [
        Message::TELEGRAM => Telegram::class,
        Message::VIBER => Viber::class
    ];

    /**
     * Создает экзепляр класса мессенджера
     * @param int $messengerId
     *
     * @return Telegram|Viber|null
     */
    public function createMessenger(int $messengerId)
    {
        $messengerClassName = null;
        if(isset(self::MESSENGER_MAPPING[$messengerId])) {
            $messengerClassName = self::MESSENGER_MAPPING[$messengerId];
        }

        if(class_exists($messengerClassName)) {
            return new $messengerClassName($this->entityManager);
        }
        return null;
    }
}