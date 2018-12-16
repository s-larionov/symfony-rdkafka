<?php declare(strict_types=1);

namespace KafkaBundle\DependencyInjection;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
class KafkaExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $manager = $container->getDefinition('kafka_manager');
        $manager->replaceArgument(2, $config['manual_rebalancing'] ?? false);

        $this->registerConsumers($manager, $config);
        $this->registerProducers($manager, $config);
    }

    protected function registerConsumers(Definition $manager, array $config): void
    {
        if (!array_key_exists('consumers', $config) || !is_array($config['consumers'])) {
            return;
        }

        foreach ($config['consumers'] as $consumerName => $consumerConfig) {
            $brokers = $consumerConfig["brokers"];
            $topic = $consumerConfig["topic"];
            $props = $consumerConfig["properties"] ?? null;
            $topicProps = $consumerConfig["topic_properties"] ?? null;
            $manager->addMethodCall('registerConsumer', [$consumerName, $brokers, $props, $topic, $topicProps]);
        }
    }

    protected function registerProducers(Definition $manager, array $config): void
    {
        if (!array_key_exists('producers', $config) || !is_array($config['producers'])) {
            return;
        }

        foreach ($config['producers'] as $producerName => $producerConfig) {
            $brokers = $producerConfig["brokers"];
            $topic = $producerConfig["topic"];
            $props = $producerConfig["properties"] ?? null;
            $topicProps = $producerConfig["topic_properties"] ?? null;
            $manager->addMethodCall('registerProducer', [$producerName, $brokers, $props, $topic, $topicProps]);
        }
    }
}
