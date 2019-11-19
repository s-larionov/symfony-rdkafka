<?php declare(strict_types=1);

namespace KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
trait PropertiesConfigurationTrait
{
    /**
     * Maximum allowed time between calls to consume messages (e.g., rd_kafka_consumer_poll()) for high-level
     * consumers. If this interval is exceeded the consumer is considered failed and the group will rebalance
     * in order to reassign the partitions to another consumer group member. Warning: Offset commits may be not
     * possible at this point. Note: It is recommended to set enable.auto.offset.store=false for long-time
     * processing applications and then explicitly store offsets (using offsets_store()) after message processing,
     * to make sure offsets are not auto-committed prior to processing has finished. The interval is checked
     * two times per second. See KIP-62 for more information.
     * Default value: 300000
     */
    protected function maxPollIntervalMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('max_poll_interval_ms');
        $node->min(1)->max(86400000)->defaultValue(300000);

        return $node;
    }

    /**
     * Maximum transmit message size.
     * Default value: 1000000
     */
    protected function messageMaxBytesNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('message_max_bytes');
        $node->min(1000)->max(1000000000);

        return $node;
    }

    /**
     * Maximum receive message size. This is a safety precaution to avoid memory exhaustion in case of protocol hickups.
     * The value should be at least fetch.message.max.bytes
     * number of partitions consumed from + messaging overhead (e.g. 200000 bytes).
     * Default value: 100000000
     */
    protected function receiveMessageMaxBytesNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('receive_message_max_bytes');
        $node->min(1000)->max(1000000000);

        return $node;
    }

    /**
     * Maximum number of in-flight requests the client will send. This setting applies per broker connection.
     * Default value: 1000000
     */
    protected function maxInFlightRequestsPerConnectionNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('max_in_flight_requests_per_connection');
        $node->min(1000)->max(1000000);

        return $node;
    }

    /**
     * Non-topic request timeout in milliseconds. This is for metadata requests, etc.
     * Default value: 60000
     */
    protected function metadataRequestTimeoutMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('metadata_request_timeout_ms');
        $node->min(10)->max(900000);

        return $node;
    }

    /**
     * Topic metadata refresh interval in milliseconds. The metadata is automatically refreshed
     * on error and connect. Use -1 to disable the intervalled refresh.
     * Default value: 300000
     */
    protected function topicMetadataRefreshIntervalMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('topic_metadata_refresh_interval_ms');
        $node->min(-1)->max(3600000);

        return $node;
    }

    /**
     * When a topic looses its leader this number of metadata requests are sent with
     * topic.metadata.refresh.fast.interval.ms interval disregarding the topic.metadata.refresh.interval.ms value.
     * This is used to recover quickly from transitioning leader brokers.
     * Default value: 10
     */
    protected function topicMetadataRefreshFastCntNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('topic_metadata_refresh_fast_cnt');
        $node->min(0)->max(1000);

        return $node;
    }

    /**
     * @see topicMetadataRefreshFastCntNodeDef() description.
     * Default value: 250
     */
    protected function topicMetadataRefreshFastIntervalMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('topic_metadata_refresh_fast_interval_ms');
        $node->min(1)->max(60000);

        return $node;
    }

    /**
     * Timeout for network requests.
     * Default value: 60000
     */
    protected function socketTimeoutMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('socket_timeout_ms');
        $node->min(0)->max(300000);

        return $node;
    }

    /**
     * Maximum time a broker socket operation may block. A lower value improves responsiveness at the
     * expense of slightly higher CPU usage.
     * Default value: 100
     */
    protected function socketBlockingMaxMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('socket_blocking_max_ms');
        $node->min(1)->max(60000);

        return $node;
    }

    /**
     * Broker socket send buffer size.
     * Default value: 0
     */
    protected function socketSendBufferBytesNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('socket_send_buffer_bytes');
        $node->min(0)->max(100000000);

        return $node;
    }

    /**
     * Broker socket receive buffer size.
     * Default value: 0
     */
    protected function socketReceiveBufferBytesNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('socket_receive_buffer_bytes');
        $node->min(0)->max(100000000);

        return $node;
    }

    /**
     * Enable TCP keep-alives (SO_KEEPALIVE) on broker sockets.
     * Default value: false
     */
    protected function socketKeepaliveEnableNodeDef(): NodeDefinition
    {
        $node = new BooleanNodeDefinition('socket_keepalive_enable');

        return $node;
    }

    /**
     * Disconnect from broker when this number of send failures (e.g., timed out requests) is reached.
     * Disable with 0. NOTE: The connection is automatically re-established.
     * Default value: 0
     */
    protected function socketMaxFailsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('socket_max_fails');
        $node->min(0)->max(1000000);

        return $node;
    }

    /**
     * How long to cache the broker address resolving results (milliseconds).
     * Default value: 100
     */
    protected function brokerAddressTtlNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('broker_address_ttl');
        $node->min(0)->max(86400000);

        return $node;
    }

    /**
     * Allowed broker IP address families: any, v4, v6.
     * Default value: any
     */
    protected function brokerAddressFamilyNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('broker_address_family');
        $node->values(array('any', 'v4', 'v6'));

        return $node;
    }

    /**
     * Protocol used to communicate with brokers.
     * Default value: plaintext
     */
    protected function securityProtocolNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('security_protocol');
        $node->values(array('plaintext', 'ssl', 'sasl_plaintext', 'sasl_ssl'));

        return $node;
    }

    /**
     * A cipher suite is a named combination of authentication, encryption, MAC and key exchange algorithm
     * used to negotiate the security settings for a network connection using TLS or SSL network protocol.
     * See manual page for ciphers(1) and `SSL_CTX_set_cipher_list(3).
     */
    protected function sslCipherSuitesNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_cipher_suites');

        return $node;
    }

    /**
     * Path to client's private key (PEM) used for authentication.
     */
    protected function sslKeyLocationNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_key_location');

        return $node;
    }

    /**
     * Private key passphrase.
     */
    protected function sslKeyPasswordNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_key_password');

        return $node;
    }

    /**
     * Path to client's public key (PEM) used for authentication.
     */
    protected function sslCertificateLocationNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_certificate_location');

        return $node;
    }

    /**
     * File or directory path to CA certificate(s) for verifying the broker's key.
     */
    protected function sslCaLocationNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_ca_location');

        return $node;
    }

    /**
     * Path to CRL for verifying broker's certificate validity.
     */
    protected function sslCrlNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('ssl_crl_location');

        return $node;
    }

    /**
     * SASL mechanism to use for authentication. Supported: GSSAPI, PLAIN.
     * NOTE: Despite the name only one mechanism must be configured.
     * Default value: GSSAPI
     */
    protected function saslMechanismsNodeDef(): NodeDefinition
    {
        $node = new EnumNodeDefinition('sasl_mechanisms');
        $node->values(array('GSSAPI', 'PLAIN'));

        return $node;
    }

    /**
     * Kerberos principal name that Kafka runs as.
     * Default value: kafka
     */
    protected function saslKerberosServiceNameNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_kerberos_service_name');

        return $node;
    }

    /**
     * This client's Kerberos principal name.
     * Default value: kafkaclient
     */
    protected function saslKerberosPrincipalNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_kerberos_principal');

        return $node;
    }

    /**
     * Full kerberos kinit command string.
     */
    protected function saslKerberosKinitCmdNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_kerberos_kinit_cmd');

        return $node;
    }

    /**
     * Path to Kerberos keytab file. Uses system default if not set.
     */
    protected function saslKerberosKeytabNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_kerberos_keytab');

        return $node;
    }

    /**
     * Minimum time in milliseconds between key refresh attempts.
     * Default value: 60000
     */
    protected function saslKerberosMinTimeBeforeReloginNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('sasl_kerberos_min_time_before_relogin');
        $node->min(1)->max(86400000);

        return $node;
    }

    /**
     * SASL username for use with the PLAIN mechanism.
     */
    protected function saslUsernameNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_username');

        return $node;
    }

    /**
     * SASL password for use with the PLAIN mechanism.
     */
    protected function saslPasswordNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('sasl_password');

        return $node;
    }

    /**
     * Client group id string. All clients sharing the same `group.id` belong to the same group.
     */
    protected function groupIdNodeDef(): NodeDefinition
    {
        $node = new ScalarNodeDefinition('group_id');

        return $node;
    }

    /**
     * Client group session and failure detection timeout.
     * Default value: 30000
     */
    protected function sessionTimeoutMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('session_timeout_ms');
        $node->min(1)->max(3600000);

        return $node;
    }

    /**
     * Group session keepalive heartbeat interval.
     * Default value: 1000
     */
    protected function heartbeatIntervalMsNodeDef(): NodeDefinition
    {
        $node = new IntegerNodeDefinition('heartbeat_interval_ms');
        $node->min(1)->max(3600000);

        return $node;
    }

    protected function getPropertiesNodeDef()
    {
        $node = new ArrayNodeDefinition('properties');

        return $node
            ->canBeUnset()
            ->children()
            ->append($this->messageMaxBytesNodeDef())
            ->append($this->receiveMessageMaxBytesNodeDef())
            ->append($this->maxInFlightRequestsPerConnectionNodeDef())
            ->append($this->metadataRequestTimeoutMsNodeDef())
            ->append($this->topicMetadataRefreshIntervalMsNodeDef())
            ->append($this->topicMetadataRefreshFastCntNodeDef())
            ->append($this->topicMetadataRefreshFastIntervalMsNodeDef())
            ->append($this->socketTimeoutMsNodeDef())
            ->append($this->socketBlockingMaxMsNodeDef())
            ->append($this->socketSendBufferBytesNodeDef())
            ->append($this->socketReceiveBufferBytesNodeDef())
            ->append($this->socketKeepaliveEnableNodeDef())
            ->append($this->socketMaxFailsNodeDef())
            ->append($this->brokerAddressTtlNodeDef())
            ->append($this->brokerAddressFamilyNodeDef())
            ->append($this->securityProtocolNodeDef())
            ->append($this->sslCipherSuitesNodeDef())
            ->append($this->sslKeyLocationNodeDef())
            ->append($this->sslKeyPasswordNodeDef())
            ->append($this->sslCertificateLocationNodeDef())
            ->append($this->sslCaLocationNodeDef())
            ->append($this->sslCrlNodeDef())
            ->append($this->saslMechanismsNodeDef())
            ->append($this->saslKerberosServiceNameNodeDef())
            ->append($this->saslKerberosPrincipalNodeDef())
            ->append($this->saslKerberosKinitCmdNodeDef())
            ->append($this->saslKerberosKeytabNodeDef())
            ->append($this->saslKerberosMinTimeBeforeReloginNodeDef())
            ->append($this->saslUsernameNodeDef())
            ->append($this->saslPasswordNodeDef())
            ->append($this->groupIdNodeDef())
            ->append($this->sessionTimeoutMsNodeDef())
            ->append($this->heartbeatIntervalMsNodeDef())
            ->append($this->maxPollIntervalMsNodeDef())
            ->end();
    }
}
