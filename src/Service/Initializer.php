<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Throwable;

final class Initializer implements InitializerInterface
{
    use CreateTableTrait;

    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array
     * @throws LogInitializationException
     * @throws ConnectionException
     * @throws Exception
     */
    public function init(): array
    {
        $result = [];

        try {
            $this->connection->beginTransaction();
            foreach (self::TABLES as $table) {
                $result[] = $this->createTable($this->connection, $table);
            }

            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new LogInitializationException(message: $exception->getMessage(), previous: $exception);
        }

        return $result;
    }
}
