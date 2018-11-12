<?php declare(strict_types=1);

namespace KafkaBundle\Producer;

use RdKafka\Conf;
use RdKafka\TopicConf;

class ProducerConfig
{
    /** @var string */
    protected $brokers;

    /** @var array */
    protected $properties = [];

    /** @var string */
    protected $topic;

    /** @var array */
    protected $topicProperties = [];

    public function __construct(
        string $brokers,
        ?array $properties,
        string $topic,
        ?array $topicProperties
    ) {
        $this->brokers = $brokers;
        $this->properties = $properties;
        $this->topic = $topic;
        $this->topicProperties = $topicProperties;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getKafkaConfig(): Conf
    {
        $conf = new Conf();

        if (null === $this->properties) {
            return $conf;
        }

        foreach ($this->properties as $property => $value) {
            $conf->set(str_replace("_", ".", $property), (string) $value);
        }

        $conf->set('metadata.broker.list', $this->brokers);
        $conf->setDefaultTopicConf($this->getTopicConfig());

        return $conf;
    }

    protected function getTopicConfig(): TopicConf
    {
        $conf = new TopicConf();

        if (null === $this->topicProperties) {
            return $conf;
        }

        foreach ($this->topicProperties as $property => $value) {
            $conf->set(str_replace("_", ".", $property), (string) $value);
        }

        return $conf;
    }
}
