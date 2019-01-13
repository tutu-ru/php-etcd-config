<?php
declare(strict_types=1);

namespace TutuRu\EtcdConfig\Exception;

use TutuRu\Config\Exception\ConfigValueUpdateExceptionInterface;

class EtcdConfigUpdateForbiddenException extends EtcdConfigException implements ConfigValueUpdateExceptionInterface
{
}
