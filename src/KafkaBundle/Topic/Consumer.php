<?php declare(strict_types=1);

namespace KafkaBundle\Topic;

use KafkaBundle\Manager\HandlerInterface;
use RdKafka\KafkaConsumer;
use RdKafka\Message;

class Consumer
{
    /** @var Config */
    protected $config;

    /** @var KafkaConsumer */
    protected $kafkaConsumer;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws \RdKafka\Exception
     */
    public function subscribe(): void
    {
        $consumer = $this->getKafkaConsumer();
        $consumer->subscribe([
            $this->config->getTopic(),
        ]);
    }

    /**
     * @param HandlerInterface $handler
     * @param int $timeout
     * @param int $retries
     *
     * @return Message
     * @throws \RdKafka\Exception
     * @throws \Exception
     */
    public function consume(HandlerInterface $handler, int $timeout, int $retries = 0): Message
    {
        $consumer = $this->getKafkaConsumer();

        $message = $consumer->consume($timeout);
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $this->processMessage($handler, $message, $retries);
                $consumer->commitAsync();
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                break;
            default:
                throw new \Exception($message->errstr(), $message->err);
                break;
        }

        return $message;
    }

    /**
     * @param HandlerInterface $handler
     * @param Message $message
     * @param int $retries
     *
     * @throws \Exception
     */
    protected function processMessage(HandlerInterface $handler, Message $message, int $retries = 0): void
    {
        $attempt = 0;

        while ($attempt <= $retries) {
            try {
                $handler->process($message);
                return;
            } catch (\Exception $e) {
                $attempt++;

                if ($attempt > $retries) {
                    throw $e;
                }
            }
        }

        throw new \Exception('Retries count has to bigger than zero');
    }

    /**
     * @throws \RdKafka\Exception
     */
    public function unsubscribe(): void
    {
        $consumer = $this->getKafkaConsumer();
        $consumer->unsubscribe();
    }

    public function isSubscribed(): bool
    {
        $consumer = $this->getKafkaConsumer();

        return !empty($consumer->getSubscription());
    }

    protected function getKafkaConsumer(): KafkaConsumer
    {
        if (null !== $this->kafkaConsumer) {
            return $this->kafkaConsumer;
        }

        $this->kafkaConsumer = new KafkaConsumer($this->config->getKafkaConfig());

        return $this->kafkaConsumer;
    }
}
