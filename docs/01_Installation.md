Installation
============

* Check dependencies are installed :

    * Symfony2
    * PHP 5.3.2

* add the FOSUserBundle bundle (user management) and follow FOSUserBundle README

        git submodule add git://github.com/FriendsOfSymfony/UserBundle.git src/FOS/UserBundle

* add the SonataEasyExtendsBundle bundle (user management) and follow EasyExtendsBundle README

        git submodule add git://github.com/sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle

* add the SonataMediaBundle bundle (media management) and follow MediaBundle README

        git submodule add git://github.com/sonata-project/MediaBundle.git src/Sonata/MediaBundle


* add the following bundle in your kernel::registerBundles() method

        new FOS\UserBundle\FOSUserBundle(),
        new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),

        new Sonata\ProductBundle\SonataProductBundle(),
        new Sonata\BasketBundle\SonataBasketBundle(),
        new Sonata\OrderBundle\SonataOrderBundle(),
        new Sonata\InvoiceBundle\SonataInvoiceBundle(),
        new Sonata\MediaBundle\SonataMediaBundle(),
        new Sonata\DeliveryBundle\SonataDeliveryBundle(),
        new Sonata\PaymentBundle\SonataPaymentBundle(),


* run the easy-extends:generate command, this command will generate the Application entities required by the Sonata's Bundles

        php yourproject/console sonata:easy-extends:generate

* then add the following bundles in your kernel::registerBundles() method

        new Application\Sonata\DeliveryBundle\SonataDeliveryBundle(),
        new Application\Sonata\BasketBundle\SonataBasketBundle(),
        new Application\Sonata\InvoiceBundle\SonataInvoiceBundle(),
        new Application\Sonata\MediaBundle\SonataMediaBundle(),
        new Application\Sonata\OrderBundle\SonataOrderBundle(),
        new Application\Sonata\PaymentBundle\SonataPaymentBundle(),
        new Application\Sonata\ProductBundle\SonataProductBundle(),

  You can use this bundle to extends entities or template files

* add the following autoload information into the autoload.php file

        // sonata core bundle
        'Sonata\\BasketBundle'               => __DIR__.'/vendor/sonata/src',
        'Sonata\\Component'                  => __DIR__.'/vendor/sonata/src',
        'Sonata\\Contrib'                    => __DIR__.'/vendor/sonata/src',
        'Sonata\\CustomerBundle'             => __DIR__.'/vendor/sonata/src',
        'Sonata\\DeliveryBundle'             => __DIR__.'/vendor/sonata/src',
        'Sonata\\InvoiceBundle'              => __DIR__.'/vendor/sonata/src',
        'Sonata\\OrderBundle'                => __DIR__.'/vendor/sonata/src',
        'Sonata\\PaymentBundle'              => __DIR__.'/vendor/sonata/src',
        'Sonata\\ProductBundle'              => __DIR__.'/vendor/sonata/src',
        'Sonata'                             => __DIR__,
        'FOS'                                => __DIR__,

* edit your config.yml and add the following lines

        sonata_delivery:
            pool: # all available delivery method
                class: Sonata\Component\Delivery\Pool
                methods:
                    - { id: free, name: Free, enabled: true, class: Sonata\Component\Delivery\FreeDelivery }

            selector:
                class: Sonata\Component\Delivery\Selector

        sonata_payment:
            methods:
                - { id: free, name: Free, enabled: true, class: Sonata\Component\Payment\Free }

        sonata_basket
            class: Sonata\Component\Basket\Basket


        sonata_product:
            products:
                - { id: bottle, name: Bottle, enabled: true, class: Application\Sonata\ProductBundle\Entity\Bottle }

            class:
                model:
                    user: Application\Sonata\UserBundle\Entity\User # you must define your own user class

        sonata_payment:
            methods:
                free:
                    name: Free
                    enabled: true
                    class: Sonata\Component\Payment\Free
                    transformers:
                        basket: sonata.transformer.basket
                        order: sonata.transformer.order
            selector:
                class: Sonata\Component\Payment\Selector

* add the current lines in your routing.yml files

        # sonata front controller
        sonata_user:
            resource: @SonataUserBundle/Resources/config/routing/user.xml
            prefix: /shop/user

        sonata_order:
            resource: @SonataOrderBundle/Resources/config/routing/order.xml
            prefix: /shop/user/invoice

        sonata_product:
            resource: @SonataProductBundle/Resources/config/routing/product.xml
            prefix: /shop/product

        sonata_category:
            resource: @SonataProductBundle/Resources/config/routing/category.xml
            prefix: /shop/category

        sonata_payment:
            resource: @SonataPaymentBundle/Resources/config/routing/payment.xml
            prefix: /shop/payment

        sonata_invoice:
            resource: @SonataInvoiceBundle/Resources/config/routing/invoice.xml
            prefix: /shop/user/invoice

* add these lines into the admin (AdminBundle)

        product:
            label:      Product
            group:      Shop
            class:      Sonata\ProductBundle\Admin\ProductAdmin
            entity:     Application\Sonata\ProductBundle\Entity\Product
            controller: SonataProductBundle:ProductAdmin
            children:
                product_delivery:
                    label:      Product Delivery
                    group:      Shop
                    class:      Sonata\ProductBundle\Admin\ProductDeliveryAdmin
                    entity:     Application\Sonata\ProductBundle\Entity\Delivery
                    controller: SonataProductBundle:ProductDeliveryAdmin

        order:
            label:      Order
            group:      Shop
            class:      Sonata\OrderBundle\Admin\OrderAdmin
            entity:     Application\Sonata\OrderBundle\Entity\Order
            controller: SonataOrderBundle:OrderAdmin
            children:
                order_element:
                    label:      Order Element
                    group:      Shop
                    class:      Sonata\OrderBundle\Admin\OrderElementAdmin
                    entity:     Application\Sonata\OrderBundle\Entity\OrderElement
                    controller: SonataOrderBundle:OrderElementAdmin

        order_element:
            label:      Order Element
            group:      Shop
            class:      Sonata\OrderBundle\Admin\OrderElementAdmin
            entity:     Application\Sonata\OrderBundle\Entity\OrderElement
            controller: SonataOrderBundle:OrderElementAdmin
            options:
                show_in_dashboard: false

        customer:
            label:      Customer
            group:      Shop
            class:      Sonata\CustomerBundle\Admin\CustomerAdmin
            entity:     Application\Sonata\CustomerBundle\Entity\Customer
            controller: SonataCustomerBundle:CustomerAdmin
            children:
                order:
                    label:      Order
                    group:      Shop
                    class:      Sonata\OrderBundle\Admin\OrderAdmin
                    entity:     Application\Sonata\OrderBundle\Entity\Order
                    controller: SonataOrderBundle:OrderAdmin

                address:
                    label:      Address
                    group:      Shop
                    class:      Sonata\CustomerBundle\Admin\AddressAdmin
                    entity:     Application\Sonata\CustomerBundle\Entity\Address
                    controller: SonataCustomerBundle:AddressAdmin