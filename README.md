DlcDoctrine
===========

Some (abstract) classes and extensions for the Zend Framework 2 Module for Doctrine ORM.

This module is currently under heavy development.

Requirements
------------
* [DlcBase](https://github.com/dlabas/DlcBase)
* [DoctrineModule](https://github.com/doctrine/DoctrineModule)
* [DoctrineORMModule](https://github.com/doctrine/DoctrineORMModule)

Installation
------------

### Main Setup

#### By cloning project

1. Install the [DlcBase](https://github.com/dlabas/DlcBase) ZF2 module
   by cloning it into `./vendor/`.
2. Clone this project into your `./vendor/` directory.

#### With composer

Coming soon...

#### Post installation

1. Enabling it in your `application.config.php`file.

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'DlcBase',
            'DlcDoctrine',
        ),
        // ...
    );

2. Add the [Add EventSubscriber to ResolveTargetEntityListener]" (https://github.com/dlabas/doctrine2/commit/6fbd7adc2a6b93cc6eeb6cedec49daef9f469db3) bugfix to your doctrine 2 module
 
