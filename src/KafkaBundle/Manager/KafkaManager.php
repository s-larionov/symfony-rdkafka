<?php declare(strict_types=1);

namespace KafkaBundle\Manager;

use KafkaBundle\Topic\Consumer;
use KafkaBundle\Topic\Producer;
use KafkaBundle\Topic\Config;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class KafkaManager
{
    /** @var Consumer[] */
    protected $consumers = [];

    /** @var Producer[] */
    protected $producers = [];

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $manualRebalancing;

    public function __construct(LoggerInterface $logger = null, bool $manualRebalancing = false)
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;
        $this->manualRebalancing = $manualRebalancing;
    }

    public function registerConsumer(string $name, string $brokers, ?array $properties, string $topic, ?array $topicProperties): self
    {
        $config = new Config($brokers, $properties, $topic, $topicProperties);

        $this->consumers[$name] = new Consumer($config, $this->logger);

        return $this;
    }

    public function getConsumer(string $name): ?Consumer
    {
        if (!isset($this->consumers[$name])) {
            return null;
        }

        return $this->consumers[$name];
    }

    public function registerProducer(string $name, string $brokers, ?array $properties, string $topic, ?array $topicProperties): self
    {
        $config = new Config($brokers, $properties, $topic, $topicProperties);

        $this->producers[$name] = new Producer($config);

        return $this;
    }

    public function getProducer(string $name): ?Producer
    {
        if (!isset($this->producers[$name])) {
            return null;
        }

        return $this->producers[$name];
    }
}
