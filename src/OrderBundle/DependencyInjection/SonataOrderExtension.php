<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\OrderBundle\DependencyInjection;

use Sonata\Doctrine\Mapper\Builder\OptionsBuilder;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataOrderExtension extends Extension
{
    /**
     * Loads the order configuration.
     *
     * @param array            $configs   An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $bundles = $container->getParameter('kernel.bundles');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('block.xml');
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('twig.xml');

        if (isset($bundles['FOSRestBundle'], $bundles['NelmioApiDocBundle'])) {
            $loader->load('api_controllers.xml');
            $loader->load('serializer.xml');
        }

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.xml');
        }

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
    }

    public function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.order.order.class', $config['class']['order']);
        $container->setParameter('sonata.order.order_element.class', $config['class']['order_element']);

        $container->setParameter('sonata.order.admin.order.entity', $config['class']['order']);
        $container->setParameter('sonata.order.admin.order_element.entity', $config['class']['order_element']);
    }

    public function registerDoctrineMapping(array $config): void
    {
        if (!class_exists($config['class']['order'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation(
            $config['class']['order'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('orderElements', $config['class']['order_element'])
                ->cascade(['persist'])
                ->mappedBy('order')
        );

        $collector->addAssociation(
            $config['class']['order'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('customer', $config['class']['customer'])
                ->inversedBy('orders')
                ->addJoin([
                    'name' => 'customer_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                ])
        );

        $collector->addAssociation(
            $config['class']['order_element'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('order', $config['class']['order'])
                ->inversedBy('orderElements')
                ->addJoin([
                    'name' => 'order_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addIndex($config['class']['order_element'], 'product_type', [
            'product_type',
        ]);

        $collector->addIndex($config['class']['order_element'], 'order_element_status', [
            'status',
        ]);

        $collector->addIndex($config['class']['order'], 'order_status', [
            'status',
        ]);

        $collector->addIndex($config['class']['order'], 'payment_status', [
            'payment_status',
        ]);
    }
}
