<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 13:59
 */

namespace App\Service;


use App\Entity\MessageRequestData;
use App\Validation\MessageValidation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Класс отвечающий за логику отправки сообщения в очередь или напрямую получателю
 * Class MessageManager
 * @package App\Service
 */
class MessageManager
{
    /** @var MessageRequestData Данные для отправки сообщений  */
    private $messageData = [];

    /** @var bool Флаг для определения, нужно ли отправлять сообщение прямо сейчас или класть в очередь */
    public $isSendNow = false;

    /** @var EntityManager  */
    public $entityManager;

    /** @var array Не доставленые сообщения */
    public $notDeliveredMessages = [];
    public $deliveredMessages = [];

    /**
     * MessageManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $isSendNow
     * @return $this
     */
    public function setIsSendNow($isSendNow) : self
    {
        $this->isSendNow = $isSendNow;
        return $this;
    }

    /**
     * @param $messageData
     * @return $this
     */
    public function setMessageData($messageData) : self
    {
        $this->messageData = $messageData;
        return $this;
    }

    /**
     * Валидирует данные
     * @param array $data
     * @return array
     */
    public function validateMessage(array $data) : array
    {
        $status = Response::HTTP_OK;
        $response = ['status' => $status ];
        foreach($data['users'] as $oneUserData)
        {
            $messageData = (new MessageRequestData());
            $messageData->setText($data['message']);
            $messageData->setChatId($oneUserData['chat_id']);
            $messageData->setMessengerId($oneUserData['messenger_id']);

            $messageValidation = new MessageValidation($messageData);
            $isValidData = $messageValidation->validate();
            if($isValidData === false) {
                $errors[] = $messageValidation->getValidationErrors();
            }
        }

        if(!empty($errors)) {
            $response['status'] = Response::HTTP_NOT_ACCEPTABLE;
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Отправляет сообщение или добавляет в очередь
     * @param array $data
     */
    public function sendMessage(array $data) : void
    {
        foreach($data['users'] as $oneUserData)
        {
            $messageDataObject = $this->createMessageObject($oneUserData, $data['message']);
            $isSendNow = $data['send_now'] ?? false;
            $this
                ->setMessageData($messageDataObject)
                ->setIsSendNow($isSendNow)
                ->sendMessageOrAddToQueue();
        }
    }


    /**
     * Отправляет сообщение, если таковое не было до этого отправлено
     */
    public function sendMessageNow() : void
    {
        if(!isset($this->messageData->messenger_id)) {
            return;
        }
        $messenger = (new MessengerFactory($this->entityManager))->createMessenger($this->messageData->messenger_id);
        $messenger->setMessageData($this->messageData);
        $isMessageAlreadySent = $messenger->checkIsAlreadySendEqualMessage();
        if(!$isMessageAlreadySent) {
            $messenger->sendMessage()
                      ->saveMessage();
        }
    }

    /**
     * Определяет, какие сообщени ябыли доставлены, а какие нет
     * @param array $deliveryStatusData
     * @return MessageManager
     */
    public function checkMessageDelivery(array $deliveryStatusData) : self
    {
        if(!isset($deliveryStatusData['statusData'])) {
            return $this;
        }

        foreach ($deliveryStatusData['statusData'] as $oneItem) {
            if ($oneItem['status'] === false) {
                $this->notDeliveredMessages[] = $oneItem;
            } else {
                $this->deliveredMessages[] = $oneItem;
            }
        }

        return $this;
    }

    /**
     * Рассылает повтороно сообщения, которые не были доставлены с первого раза
     * @return MessageManager
     */
    public function sendNotDeliveredMessages() : self
    {
        foreach($this->notDeliveredMessages as $oneMessage) {
            $this->setMessageData($oneMessage)
                  ->sendMessageNow();
        }
        return $this;
    }


    /**
     * Добавляет сообщение в очередь
     */
    private function sendMessageOrAddToQueue() : void
    {
        if($this->isSendNow) {
            $this->sendMessageNow();
            return;
        }

        $queue = new MessageQueue();
        $queue->addToQueue((array)$this->messageData);
    }

    /**
     * @param array $userData
     * @param string $messageText
     * @return MessageRequestData
     */
    private function createMessageObject(array $userData,string $messageText) : MessageRequestData
    {
        $messageData = (new MessageRequestData());
        $messageData->setText($messageText);
        $messageData->setChatId($userData['chat_id']);
        $messageData->setMessengerId($userData['messenger_id']);
        return $messageData;
    }


}