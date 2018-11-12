<?php declare(strict_types=1);

namespace KafkaBundle\Topic;

use RdKafka\Conf;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Producer
{
    public const PARTITION_UNASSIGNED = RD_KAFKA_PARTITION_UA;

    /** @var Config */
    protected $config;

    /** @var KafkaProducer */
    protected $producer;

    /** @var ProducerTopic */
    protected $producerTopic;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function produce(string $payload, int $partition = self::PARTITION_UNASSIGNED, ?string $key = null): void
    {
        $producer = $this->getKafkaProducer();
        $topic = $this->getTopicProducer();

        $topic->produce($partition, 0, $payload, $key);
        $producer->poll(0);
    }

    public function isProducing(): bool
    {
        return null !== $this->producerTopic && null !== $this->producer;
    }

    public function stop(): void
    {
        if (!$this->isProducing()) {
            return;
        }

        $this->pollAllMessages();
        $this->producerTopic = null;
        $this->producer = null;
    }

    public function pollAllMessages(): void
    {
        if (!$this->isProducing()) {
            return;
        }

        $producer = $this->getKafkaProducer();
        while ($producer->getOutQLen() > 0) {
            $producer->poll(0);
        }
    }

    public function __destruct()
    {
        $this->pollAllMessages();
    }

    protected function getKafkaProducer(): KafkaProducer
    {
        if (null !== $this->producer) {
            return $this->producer;
        }

        $this->producer = new KafkaProducer($this->config->getKafkaConfig());

        return $this->producer;
    }

    protected function getTopicProducer(): ProducerTopic
    {
        if (null !== $this->producerTopic) {
            return $this->producerTopic;
        }

        $producer = $this->getKafkaProducer();
        $this->producerTopic = $producer->newTopic($this->config->getTopic(), $this->config->getTopicConfig());

        return $this->producerTopic;
    }
}
