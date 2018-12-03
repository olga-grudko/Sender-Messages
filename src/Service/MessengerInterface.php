<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 02.12.18
 * Time: 14:26
 */

namespace App\Service;

/**
 * Интерфейс для мессенджеров
 * Interface MessengerInterface
 *
 * @package App\Service\Messengers
 */
interface MessengerInterface
{
    public function sendMessage();
}