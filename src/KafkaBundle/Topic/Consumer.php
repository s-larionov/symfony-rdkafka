<?php declare(strict_types=1);

namespace KafkaBundle\Topic;

use KafkaBundle\Manager\HandlerInterface;
use Psr\Log\LoggerInterface;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\TopicPartition;

class Consumer
{
    /** @var Config */
    protected $config;

    /** @var KafkaConsumer */
    protected $kafkaConsumer;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
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
                $consumer->commit($message);
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
        $consumer->assign(null);
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

        $config = $this->config->getKafkaConfig();
        $config->setRebalanceCb(function (KafkaConsumer $kafka, int $error, array $partitions = null): void {
            /** @var TopicPartition $partitions */
            switch ($error) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $this->logger->info('Assign partitions', [
                        'partitions' => json_encode($this->extractPartitionsInfo($partitions)),
                    ]);
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $this->logger->info('Revoke partitions', [
                        'partitions' => json_encode($this->extractPartitionsInfo($partitions)),
                    ]);
                    break;
                default:
                    $this->logger->error('Rebalance error', [
                        'error_code' => $error,
                        'partitions' => json_encode($this->extractPartitionsInfo($partitions)),
                    ]);
                    $kafka->assign(NULL);
            }
        });
        $config->setErrorCb(function(KafkaConsumer $consumer, int $error, string $reason) {
            $this->logger->error('KafkaConsumer error', [
                'error_code' => $error,
                'error_msg' => $reason,
                'partitions' => $this->extractPartitionsInfo($consumer->getAssignment()),
            ]);
        });

        $this->kafkaConsumer = new KafkaConsumer($config);

        return $this->kafkaConsumer;
    }

    /**
     * @param TopicPartition[]|null $partitions
     *
     * @return array|null
     */
    protected function extractPartitionsInfo(array $partitions = null): ?array
    {
        if (null === $partitions) {
            return null;
        }

        $result = [];

        foreach ($partitions as $partition) {
            $result[] = [
                'topic' => $partition->getTopic(),
                'partition' => $partition->getPartition(),
                'offset' => $partition->getOffset(),
            ];
        }

        return $result;
    }
}
