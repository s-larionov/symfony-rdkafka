<?php declare(strict_types=1);

namespace KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
trait TopicProducerPropertiesConfiguration
{
    /**
     * This field indicates how many acknowledgements the leader broker must receive from ISR brokers
     * before responding to the request:
     *   0=Broker does not send any response/ack to client,
     *   1=Only the leader broker will need to ack the message,
     *   -1 or all=broker will block until message is committed by all in sync replicas
     *    (ISRs) or broker's `in.sync.replicas` setting before sending response.
     * Default value: 1
     */
    protected function requestRequiredAcksNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('request_required_acks');
        $node->min(-1)->max(1000);

        return $node;
    }

    /**
     * The ack timeout of the producer request in milliseconds. This value is only enforced by the broker and
     * relies on `request.required.acks` being > 0.
     * Default value: 5000
     */
    protected function requestTimeoutMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('request_timeout_ms');
        $node->min(1)->max(900000);

        return $node;
    }

    /**
     * Local message timeout. This value is only enforced locally and limits the time a produced message waits
     * for successful delivery. A time of 0 is infinite.
     * Default value: 300000
     */
    protected function messageTimeoutMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('message_timeout_ms');
        $node->min(0)->max(900000);

        return $node;
    }

    /**
     * Report offset of produced message back to application.
     * Default value: false
     */
    protected function produceOffsetReportNodeDef(): NodeDefinition
    {
        $node = new BooleanNodeDefinition('produce_offset_report');

        return $node;
    }

    /**
     * Compression codec to use for compressing message sets.
     * Default value: inherit
     */
    protected function compressionCodecNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('compression_codec');
        $node->values(array('none', 'gzip', 'snappy', 'lz4', 'inherit'));

        return $node;
    }


    protected function getTopicProducerPropertiesNodeDef(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('topic_properties');

        return $node
            ->canBeUnset()
            ->children()
            ->append($this->requestRequiredAcksNodeDef())
            ->append($this->requestTimeoutMsNodeDef())
            ->append($this->messageTimeoutMsNodeDef())
            ->append($this->produceOffsetReportNodeDef())
            ->append($this->compressionCodecNodeDef())
            ->end();
    }

}
