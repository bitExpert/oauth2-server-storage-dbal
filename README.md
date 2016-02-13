# bitexpert/oauth2-server-storage-dbal
This package provides Dbal storage implementations for [PHP OAuth 2.0 Server](https://github.com/thephpleague/oauth2-server).

[![Build Status](https://travis-ci.org/bitExpert/oauth2-server-storage-dbal.svg?branch=master)](https://travis-ci.org/bitExpert/oauth2-server-storage-dbal)

Installation
------------

The preferred way of installing `bitexpert/oauth2-server-storage-dbal` is through Composer. Simply add 
`bitexpert/oauth2-server-storage-dbal` as a dependency:

```
composer.phar require bitexpert/oauth2-server-storage-dbal
```

Usage
-----

Either create a \Doctrine\DBAL\Connection instance yourself or grab it from the \Doctrine\ORM\EntityManager in case
you are using Doctrine ORM:

```
/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = ...
$connection = $entityManager->getConnection();
```

Pass the $connection instance to the *Storage implementations and register those with the \League\OAuth2\Server\AuthorizationServer
instance:

```
$server = new \League\OAuth2\Server\AuthorizationServer();
$server->setSessionStorage(new \bitExpert\\OAuth2\Server\Storage\Dbal\SessionStorage($connection));
$server->setAccessTokenStorage(new \bitExpert\OAuth2\Server\Storage\Dbal\AccessTokenStorage($connection));
$server->setClientStorage(new \bitExpert\OAuth2\Server\Storage\Dbal\ClientStorage($connection));
$server->setScopeStorage(new \bitExpert\OAuth2\Server\Storage\Dbal\ScopeStorage($connection));
```

The required database schema can be found in scripts/setup.php.

License
-------

OAuth2-Server-Storage-Dbal is released under the Apache 2.0 license.
