<?php
declare(strict_types=1);

namespace TutuRu\EtcdConfig\Exception;

use TutuRu\Config\Exception\InvalidConfigExceptionInterface;

class EtcdConfigLoadingException extends EtcdConfigException implements InvalidConfigExceptionInterface
{
}
