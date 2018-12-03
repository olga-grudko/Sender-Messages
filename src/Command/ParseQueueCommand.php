<?php
/**
 * Created by PhpStorm.
 * User: olga
 * Date: 04.12.18
 * Time: 0:48
 */

namespace App\Command;

use App\Service\MessageManager;
use App\Service\MessageQueue;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseQueueCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:parse-queue')
            ->setDescription('Разбирает очередь сообщений');
    }

    /**
     * Разбор очереди по крону
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = new MessageQueue();
        $messageData = $queue->popCount(MessageQueue::DEFAULT_COUNT);

        $entityManager = $this->getContainer()->get('Doctrine\ORM\EntityManager');
        $messageManager = new MessageManager($entityManager);
        foreach ($messageData['users'] as $oneData) {
            $messageManager->setMessageData($oneData)
                ->sendMessageNow();
        }
        $output->writeln('success');
    }
}