<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 01.12.18
 * Time: 23:07
 */

namespace App\Controller;


use App\Auth\Auth;
use App\Service\Message;
use App\Service\MessageManager;
use App\Service\MessageQueue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для отправки сообщений (в очередь или напрямую)
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractController
{
    public $entityManager;

    /**
     * ApiController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface  $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/sendMessage", name="send_message")
     * @param Request $request
     *
     * @return Response
     */
    public function sendMessage(Request $request)
    {
        $headers = $request->headers->all();
        if (!(new Auth($headers['token'][0]))->isValidToken()) {
            return new Response(
                json_encode(['status' => Response::HTTP_UNAUTHORIZED])
            );
        }

        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['users'])) {
            return new Response(
                json_encode(['status' => Response::HTTP_BAD_REQUEST])
            );
        }


        $messageManager = new MessageManager($this->entityManager);
        $response = $messageManager->validateAndSendMessage($requestData);

        return new Response(
            json_encode($response)
        );

    }

    /**
     * Разбор очереди по крону
     *
     * @Route("/parseQueue", name="parse_queue")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function parseQueue(Request $request)
    {
        $headers = $request->headers->all();
        if (!(new Auth($headers['token'][0]))->isValidToken()) {
            return new Response(
                json_encode(['status' => Response::HTTP_UNAUTHORIZED])
            );
        }

        $queue = new MessageQueue();
        $messageData = $queue->popCount(MessageQueue::DEFAULT_COUNT);

        /*
         *заглушка
         $messageData = [
            'users' =>
                [
                    ['chat_id' => 1, 'messenger_id' => 2,],
                    ['chat_id' => 2, 'messenger_id' => 1 ]
                ],

            'message' => 'custom message'];
         */

        $messageManager = new MessageManager($this->entityManager);
        foreach ($messageData['users'] as $oneData) {
            $messageManager->setMessageData($oneData)
                ->sendMessageNow();
        }

        return new Response(
            json_encode(['status' => Response::HTTP_OK])
        );

    }


    /**
     * Сюда присылается ответ о статусе доставки сообщений
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function callBackFromMessenger(Request $request, EntityManagerInterface  $entityManager)
    {
        $headers = $request->headers->all();
        if (!(new Auth($headers['token'][0]))->isValidToken()) {
            return new Response(
                json_encode(['status' => Response::HTTP_UNAUTHORIZED])
            );
        }

        $requestData = json_decode($request->getContent(), true);

        $messageManager = new MessageManager($this->entityManager);
        $messageManager->checkMessageDelivery($requestData)
                       ->sendNotDeliveredMessages();

        $message = new Message($entityManager);
        $message->setMessageIsDelivered($messageManager->deliveredMessages);

        return new Response(
            json_encode(['status' => Response::HTTP_OK])
        );
    }
}