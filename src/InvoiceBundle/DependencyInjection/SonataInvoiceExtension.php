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

namespace Sonata\InvoiceBundle\DependencyInjection;

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
class SonataInvoiceExtension extends Extension
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
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('renderer.xml');

        if (isset($bundles['FOSRestBundle'], $bundles['NelmioApiDocBundle'])) {
            $loader->load('api_controllers.xml');
            $loader->load('serializer.xml');
        }

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.xml');
        }

        $this->registerParameters($container, $config);
        $this->registerDoctrineMapping($config);
    }

    public function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.invoice.invoice.class', $config['class']['invoice']);
        $container->setParameter('sonata.invoice.invoice_element.class', $config['class']['invoice_element']);

        $container->setParameter('sonata.invoice.admin.invoice.entity', $config['class']['invoice']);
        $container->setParameter('sonata.invoice.admin.invoice_element.entity', $config['class']['invoice_element']);
    }

    public function registerDoctrineMapping(array $config): void
    {
        if (!class_exists($config['class']['invoice'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        /*
         * INVOICE
         */
        $collector->addAssociation(
            $config['class']['invoice'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('customer', $config['class']['customer'])
                ->cascade(['persist', 'refresh', 'merge', 'detach'])
                ->addJoin([
                    'name' => 'customer_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                ])
    );

        $collector->addAssociation(
            $config['class']['invoice_element'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('invoice', $config['class']['invoice'])
                ->cascade(['persist', 'refresh', 'merge', 'detach'])
                ->inversedBy('invoiceElements')
                ->addJoin([
                    'name' => 'invoice_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['invoice'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('invoiceElements', $config['class']['invoice_element'])
                ->cascade(['persist'])
                ->mappedBy('invoice')
                ->orphanRemoval()
        );

        $collector->addAssociation(
            $config['class']['invoice_element'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('orderElement', $config['class']['order_element'])
                ->addJoin([
                    'name' => 'order_element_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );
    }
}
