<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

trait CreateTableTrait
{
    /**
     * @param Connection $connection
     * @param string $tableName
     * @return string
     * @throws Exception
     */
    private function createTable(Connection $connection, string $tableName): string
    {
        $connection->executeStatement(
            sprintf(
                'CREATE TABLE IF NOT EXISTS %s (id SERIAL NOT NULL, level VARCHAR(255) DEFAULT NULL,'
                . ' message TEXT DEFAULT NULL, context JSONB DEFAULT NULL,'
                . ' created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))',
                $tableName,
            ),
        );
        $connection->executeStatement(
            sprintf(
                'CREATE INDEX IF NOT EXISTS idx_%s_level ON %s (level)',
                $tableName,
                $tableName,
            ),
        );
        $connection->executeStatement(
            sprintf(
                'CREATE INDEX IF NOT EXISTS idx_%s_created_at ON %s (created_at)',
                $tableName,
                $tableName,
            ),
        );
        return sprintf('Table `%s` created.', $tableName);
    }
}
