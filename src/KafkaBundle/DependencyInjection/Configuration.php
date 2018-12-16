<?php declare(strict_types=1);

namespace KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Mike Shauneu <mike.shauneu@gmail.com>
 * @author Sergey Larionov <sergey@larionov.biz>
 */
class Configuration implements ConfigurationInterface
{
    use PropertiesConfigurationTrait;
    use TopicConsumerPropertiesConfiguration;
	use TopicProducerPropertiesConfiguration;

    /**
     * {@inheritDoc}
     * @see ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('kafka');

        $rootNode
            ->children()
                ->booleanNode('manual_rebalancing')->defaultTrue()->end()
                ->arrayNode('producers')
                    ->canBeUnset()
                    ->prototype('array')
                    ->children()
                        ->scalarNode('brokers')->isRequired()->end()
                        ->scalarNode('topic')->isRequired()->end()
                        ->append($this->getPropertiesNodeDef())
                        ->append($this->getTopicProducerPropertiesNodeDef())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('consumers')
                    ->canBeUnset()
                    ->prototype('array')
                    ->children()
                        ->scalarNode('brokers')->isRequired()->end()
                        ->scalarNode('topic')->isRequired()->end()
                        ->append($this->getPropertiesNodeDef())
                        ->append($this->getTopicConsumerPropertiesNodeDef())
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $tree;
    }

}
