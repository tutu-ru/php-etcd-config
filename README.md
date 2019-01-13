# Библиотека EtcdConfig

Реализация конфига хранящегося в etcd.
Возможно использовать отдельно или с библиотекой [tutu-ru/php-config](https://github.com/tutu-ru/php-config) через интерфейс `TutuRu\Config\ConfigInterface`.

## Инициализация и использование

Конфиг загружается сразу при создании объекта.

Создание конфига:
```php
use TutuRu\EtcdConfig\EtcdConfig;

$config = new EtcdConfig('/config/root/node');
$config->getValue('some.node');
```

Создание конфига с кэшированием данных на 60 секунд:
```php
use TutuRu\EtcdConfig\EtcdConfig;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;

$cache = new SimpleCacheBridge(new ApcuCachePool());
$config = new EtcdConfig('/config/root/node', $cache, 60);
$config->getValue('some.node');
```

Создание конфига с возможностью изменения в рантайме:
```php
use TutuRu\EtcdConfig\MutableEtcdConfig;

$config = new MutableEtcdConfig('/config/root/node');
$config->setValue('some.node', 'new value');
```

## Миграции

```php
use TutuRu\EtcdConfig\EtcdConfigMutator;

$configMutator = new EtcdConfigMutator('/config/root/node');

$configMutator->init();
$configMutator->setValue('some/node', $value);
```
