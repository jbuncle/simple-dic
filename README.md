# PHP Simple DIC

A simple dependency injection container with focus on automatic wiring (auto-wiring)
dependencies.

## Introduction

Simple DIC is implemented with auto-wiring at the forefront. 

This basically means that if a class can be created by automatically by recursively resolving dependencies then you will 
get a class back, even if you haven't manually put anything into the container!

For this to happen fully 100% automatically, all class constructor argumentss will need to have explicit type hints for 
instantiable types (i.e. they need to be non-abstract, non-interface, non-native). 

If the types required are using interfaces and/or abstract classes then you can define a type mapping with the 
`addTypeMapping` method, to tell the container what concrete classes to resolve the types to.

If the types required have dependencies on native types (e.g. the constructor requires a string), then you can define factory
methods to create the instance manually. You're factory method can define arguments which will also be autowired.

I have tried to optimise this implementation as much as possible.

**Example**

In the example below we are able to get an instance of the `Foo` class, simply be asking the container for one, 
no setup of the container is required other than instantiating it.

```php

class Baz {

    public __construct();
}

class Bar {

    public __construct(Baz baz);
}

class Foo {

    public __construct(Bar bar);
}

$container = new \JBuncle\SimpleDic\Container();
$fooInstance = $container->getInstance(Foo::class);
```

This will automatically instantiate instances of `Foo`, `Bar` and `Baz`, and store those instances in the container 
automatically. Therefore subsequently calling `$container->getInstance(Bar::class)` will give you the same class
instance used to create `Foo`.


## Adding Factory Functions

Factory functions allow you define a callback to use to manually create instances (lazily) when needed.

Useful for instances that cannot be created using full automatic autowiring.

## Type Mapping

Type mapping allows you to tell the container what type to use when a specific type is requested.

This is useful for when you want to define what concrete or extending class to use when a supertype or interface is required.

## PSR Compatibility

A simple DIC can be easily converted into a PHP FIG PSR-11 (`psr/container` v2) compatible implementation using the 
`PsrAdapter` wrapper class:

```php
$containerFactory = new \JBuncle\SimpleDic\ContainerFactory();
$container = $containerFactory->create();

/* @var $psrContainer \Psr\Container\ContainerInterface */
$psrContainer = new \JBuncle\SimpleDic\PsrAdapter($container);
```