<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 01.12.18
 * Time: 23:44
 */

namespace App\Validation;

use Symfony\Component\Validator\Validation;
use App\Entity\MessageRequestData;

/**
 * Class MessageValidation
 *
 * @package App\Validation
 */
class MessageValidation
{
    /** @var MessageRequestData  */
    public $messageData = null;
    /** @var string */
    private $validationError = null;

    public function __construct(MessageRequestData $messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * @return bool
     */
    public function validate() : bool
    {
        $validator = Validation::createValidator();
        $errors = $validator->validate( $this->messageData);
        if (count($errors) > 0) {
            $this->validationError =  (string) $errors;
           return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getValidationErrors() : ?string
    {
        return $this->validationError;
    }

}