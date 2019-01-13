# Библиотека EtcdConfig

Реализация конфига хранимого в etcd. 
Возможно использовать отдельно или с библиотекой [tutu-ru/php-config](https://github.com/tutu-ru/php-config).

## Инициализация и использование

Создание конфига:
```php
use TutuRu\EtcdConfig\EtcdConfig;

$config = new EtcdConfig('/config/root/node');
```

Создание конфига с кэшированием данных на 60 секунд:
```php
use TutuRu\EtcdConfig\EtcdConfig;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;

$cache = new SimpleCacheBridge(new ApcuCachePool());
$config = new EtcdConfig('/config/root/node', $cache, 60);
```

Далее вся работа идет в соответствии с интерфейсом `TutuRu\Config\ConfigInterface`.

## Миграции

```php
use TutuRu\EtcdConfig\EtcdConfigMutator;

$configMutator = new EtcdConfigMutator('/config/root/node');

$configMutator->init();
$configMutator->setValue('some/node', $value);
```
