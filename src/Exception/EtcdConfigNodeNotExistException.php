<?php
declare(strict_types=1);

namespace TutuRu\EtcdConfig\Exception;

use TutuRu\Config\Exception\ConfigPathNotExistExceptionInterface;

class EtcdConfigNodeNotExistException extends EtcdConfigException implements ConfigPathNotExistExceptionInterface
{
}
