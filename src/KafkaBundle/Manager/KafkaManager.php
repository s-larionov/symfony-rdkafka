<?php declare(strict_types=1);

namespace KafkaBundle\Manager;

use KafkaBundle\Topic\Consumer;
use KafkaBundle\Topic\Producer;
use KafkaBundle\Topic\Config;

class KafkaManager
{
    /** @var Consumer[] */
    protected $consumers = [];

    /** @var Producer[] */
    protected $producers = [];

    public function registerConsumer(string $name, string $brokers, ?array $properties, string $topic, ?array $topicProperties): self
    {
        $config = new Config($brokers, $properties, $topic, $topicProperties);

        $this->consumers[$name] = new Consumer($config);

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
