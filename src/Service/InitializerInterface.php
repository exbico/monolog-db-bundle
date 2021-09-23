<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;

interface InitializerInterface
{
    public const    ERROR_TABLE_NAME = 'log_error';
    public const    INFO_TABLE_NAME  = 'log_info';
    public const    TABLES           = [
        self::ERROR_TABLE_NAME,
        self::INFO_TABLE_NAME,
    ];

    /**
     * @return array
     * @throws LogInitializationException
     * @throws ConnectionException
     * @throws Exception
     */
    public function init(): array;
}
