<?php declare(strict_types=1);

namespace KafkaBundle\Command;

use KafkaBundle\Manager\HandlerInterface;
use KafkaBundle\Manager\KafkaManager;
use RdKafka\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
class ConsumerCommand extends ContainerAwareCommand
{
    /** @var KafkaManager */
    protected $kafkaManager;

    /** @inheritdoc */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->kafkaManager = $this->getContainer()->get('kafka_manager');
    }


    /** @inheritdoc */
    protected function configure()
    {
        $this
            ->setName('kafka:consumer')
            ->addOption('consumer', null, InputOption::VALUE_REQUIRED, 'Consumer')
            ->addOption('handler', null, InputOption::VALUE_REQUIRED, 'MessageHandler')
            ->addOption('timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout in ms', 1000)
            ->addOption('retries', 'r', InputOption::VALUE_OPTIONAL, 'Retries count', 10);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $input->getOption('consumer');

        $topicConsumer = $this->kafkaManager->getConsumer($consumer);
        if (null === $topicConsumer) {
            throw new \Exception(sprintf('Consumer with name "%s" is not defined', $consumer));
        }

        $handler = $input->getOption('handler');
        /** @var HandlerInterface $messageHandler */
        $messageHandler = $this->getContainer()->get($handler);
        if (null === $messageHandler) {
            throw new \Exception(sprintf('Message Handler with name "%s" is not defined', $handler));
        }

        $timeout = $input->getOption('timeout');
        if (!is_numeric($timeout)) {
            throw new \Exception('Timeout needs to be a number in the range 0..2^32-1');
        }

        $retries = $input->getOption('retries');
        if (!is_numeric($retries)) {
            throw new \Exception('Retries needs to be a number in the range 0..2^32-1');
        }

        $topicConsumer->subscribe();

        pcntl_signal(SIGTERM, [&$topicConsumer, 'unsubscribe']);
        pcntl_signal(SIGINT, [&$topicConsumer, 'unsubscribe']);
        pcntl_signal(SIGHUP, [&$topicConsumer, 'subscribe']);

        while ($topicConsumer->isSubscribed()) {
            $message = $topicConsumer->consume($messageHandler, (int) $timeout, (int) $retries);
            $this->traceMessage($message, $output);
            pcntl_signal_dispatch();
        }
    }

    protected function traceMessage(Message $message, OutputInterface $output): void
    {
        if (!$output->isVerbose()) {
            return;
        }

        if (!$output->isVeryVerbose()) {
            $output->writeln($message->payload);
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Parameter', 'Value']);
        $table->addRows([
            ['Topic', $message->topic_name ?? '{empty}'],
            ['Partition', $message->partition],
            ['Offset', $message->offset],
            ['Key', $message->key ?? '{empty}'],
            ['Payload', $message->payload ?? '{empty}'],
            ['Error', "[{$message->err}] {$message->errstr()}"],
        ]);
        $table->render();
    }
}

