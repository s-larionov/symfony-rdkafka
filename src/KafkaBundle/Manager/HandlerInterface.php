<?php declare(strict_types=1);

namespace KafkaBundle\Manager;

use RdKafka\Message;

interface HandlerInterface
{
    public function process(Message $message): void;
}
