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

namespace Sonata\BasketBundle\DependencyInjection;

use Sonata\Doctrine\Mapper\Builder\OptionsBuilder;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * BasketExtension.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataBasketExtension extends Extension
{
    /**
     * Loads the url shortener configuration.
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
        $loader->load('basket_entity.xml');
        $loader->load('basket_session.xml');
        $loader->load('basket.xml');
        $loader->load('validator.xml');
        $loader->load('form.xml');
        $loader->load('twig.xml');

        if (isset($bundles['FOSRestBundle'], $bundles['NelmioApiDocBundle'])) {
            $loader->load('api_controllers.xml');
            $loader->load('api_form.xml');
        }

        $container->setAlias('sonata.basket.builder', $config['builder']);
        $container->setAlias('sonata.basket.factory', $config['factory']);
        $container->setAlias('sonata.basket.loader', $config['loader']);

        $this->registerParameters($container, $config);
        $this->registerDoctrineMapping($config);
    }

    /**
     * @param $config
     */
    public function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.basket.basket.class', $config['class']['basket']);
        $container->setParameter('sonata.basket.basket_element.class', $config['class']['basket_element']);
    }

    public function registerDoctrineMapping(array $config): void
    {
        if (!class_exists($config['class']['basket'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation(
            $config['class']['basket'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('customer', $config['class']['customer'])
                ->addJoin([
                    'name' => 'customer_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                    'unique' => true,
                ])
        );

        $collector->addAssociation(
            $config['class']['basket'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('basketElements', $config['class']['basket_element'])
                ->cascade(['persist'])
                ->mappedBy('basket')
                ->orphanRemoval()
        );

        $collector->addAssociation(
            $config['class']['basket_element'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('basket', $config['class']['basket'])
                ->inversedBy('basketElements')
                ->addJoin([
                    'name' => 'basket_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
         );
    }
}
