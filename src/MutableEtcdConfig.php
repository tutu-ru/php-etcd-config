<?php
declare(strict_types=1);

namespace TutuRu\EtcdConfig;

use TutuRu\Config\ConfigInterface;
use TutuRu\Config\MutableConfigInterface;
use TutuRu\Etcd\EtcdClient;
use TutuRu\Etcd\Exceptions\EtcdException;
use TutuRu\EtcdConfig\Exception\EtcdConfigUpdateForbiddenException;

class MutableEtcdConfig extends EtcdConfig implements MutableConfigInterface
{
    public function setValue(string $path, $value): void
    {
        try {
            $this->client->setValue(
                str_replace(ConfigInterface::CONFIG_PATH_SEPARATOR, EtcdClient::PATH_SEPARATOR, $path),
                $value
            );
            $this->setConfigData($path, $value);
            $this->saveDataInCache($this->data);
        } catch (EtcdException $e) {
            throw new EtcdConfigUpdateForbiddenException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
