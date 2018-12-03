<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 01.12.18
 * Time: 23:53
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="messages")
 */
class MessageRequestData
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @ORM\Column(type="text")
     */
    public $text;

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @ORM\Column(type="integer")
     */
    public $chat_id;

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @ORM\Column(type="integer")
     */
    public $messenger_id;

    /**
     * @ORM\Column(type="boolean")
     */
    public $is_delivered;

    /**
     * @return mixed
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return MessageRequestData
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chat_id;
    }

    /**
     * @param int $chat_id
     * @return MessageRequestData
     */
    public function setChatId(int $chat_id): self
    {
        $this->chat_id = $chat_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getMessengerId() : int
    {
        return $this->messenger_id;
    }

    /**
     * @param int $messenger_id
     * @return MessageRequestData
     */
    public function setMessengerId(int $messenger_id): self
    {
        $this->messenger_id = $messenger_id;
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsDelivered(bool $value): self
    {
        $this->is_delivered = $value;
        return $this;
    }





}