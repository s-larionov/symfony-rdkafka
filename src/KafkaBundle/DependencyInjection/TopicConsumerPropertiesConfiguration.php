<?php declare(strict_types=1);

namespace KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
trait TopicConsumerPropertiesConfiguration
{
    /**
     * Action to take when there is no initial offset in offset store or the desired offset is out of
     * range: 'smallest','earliest' - automatically reset the offset to the smallest offset, 'largest',
     * 'latest' - automatically reset the offset to the largest offset,
     * 'error' - trigger an error which is retrieved by consuming messages and checking 'message->err'.
     * Default value: largest
     */
    protected function autoOffsetResetNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('auto_offset_reset');
        $node->values(array('smallest', 'earliest', 'largest', 'latest', 'error'));

        return $node;
    }

    /**
     * Path to local file for storing offsets. If the path is a directory a filename will be automatically
     * generated in that directory based on the topic and partition.
     * Default value: .
     */
    protected function offsetStorePathNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('offset_store_path');

        return $node;
    }

    /**
     * fsync() interval for the offset file, in milliseconds. Use -1 to disable syncing, and 0 for
     * immediate sync after each write.
     * Default value: -1
     */
    protected function offsetStoreSyncIntervalMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('offset_store_sync_interval_ms');
        $node->min(-1)->max(86400000);

        return $node;
    }

    /**
     * Offset commit store method:
     * 'file' - local file store (offset.store.path, et.al),
     * 'broker' - broker commit store (requires "group.id" to be configured and Apache Kafka 0.8.2 or later on the broker).
     * Default value: broker
     */
    protected function offsetStoreMethodNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('offset_store_method');
        $node->values(array('file', 'broker'));

        return $node;
    }

    /**
     * Maximum number of messages to dispatch (0 = unlimited)
     * Default value: 0
     */
    protected function consumeCallbackMaxMessagesNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('consume_callback_max_messages');
        $node->min(-1)->max(1000000);

        return $node;
    }

    protected function getTopicConsumerPropertiesNodeDef(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('topic_properties');

        return $node
            ->canBeUnset()
            ->children()
            ->append($this->autoOffsetResetNodeDef())
            ->append($this->offsetStorePathNodeDef())
            ->append($this->offsetStoreSyncIntervalMsNodeDef())
            ->append($this->offsetStoreMethodNodeDef())
            ->append($this->consumeCallbackMaxMessagesNodeDef())
            ->end();
    }
}
