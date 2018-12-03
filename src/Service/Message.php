<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:13
 */

namespace App\Service;


use App\Entity\MessageRequestData;
use Doctrine\ORM\EntityManager;

/**
 * Класс для работы с сообщениями по мессенджерам
 * Class Messenger
 * @package App\Service
 */
class Message
{
    const TELEGRAM = 1;
    const VIBER = 2;

    /** @var MessageRequestData */
    protected $messageData;
    /** @var EntityManager  */
    protected $entityManager;

    /**
     * Messenger Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param MessageRequestData $messageData
     * @return Message
     */
    public function setMessageData(MessageRequestData $messageData) : self
    {
        $this->messageData = $messageData;
        return $this;
    }

    /**
     * Сохраняет сообщение в бд
     * @throws \Exception
     */
    public function saveMessage() : void
    {
        try {
            $this->entityManager->persist($this->messageData);
            $this->entityManager->flush();
        }catch (\Exception $exception) {
             throw $exception;
        }
    }

    /**
     * Проверяет, было ли уже отправлено сообщение
     * @return bool
     */
    public function checkIsAlreadySendEqualMessage() : bool
    {
        $repository = $this->entityManager->getRepository(MessageRequestData::class);
        $message = $repository->findOneBy(
            ['chat_id' => $this->messageData->chat_id,
            'text' => $this->messageData->text,
            'messenger_id' => $this->messageData->messenger_id
            ]);

        if(empty($message)) {
           return false;
        }

       return true;
    }

    /**
     * Обновляет в базе статус сообщений на "доставлено"
     * @param array $messageData
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setMessageIsDelivered(array $messageData) : void
    {
        $repository = $this->entityManager->getRepository(MessageRequestData::class);
        foreach ($messageData as $oneMessage) {
            /** @var MessageRequestData $message */
            $message = $repository->findOneBy(
                ['chat_id' => $oneMessage['chat_id'],
                    'text' => $oneMessage['message'],
                    'messenger_id' => $oneMessage['messenger_id']
                ]);
            if($message) {
                $message->setIsDelivered(true);
                $this->entityManager->flush();
            }

        }
    }
}