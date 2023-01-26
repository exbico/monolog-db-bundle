<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Throwable;

use function count;

final class Rotator implements RotatorInterface
{
    use CreateTableTrait;

    private const DATE_FORMAT = 'YmdHis';

    public function __construct(private Connection $connection, private int $historySize)
    {
    }

    /**
     * @param int|null $historySize
     * @return array
     * @throws ConnectionException
     * @throws Exception
     * @throws LogRotationException
     */
    public function rotate(?int $historySize): array
    {
        $result = [];
        $historySize ??= $this->historySize;

        try {
            $this->connection->beginTransaction();
            foreach (InitializerInterface::TABLES as $table) {
                $result[] = $this->renameTable($this->connection, $table);
                $result[] = $this->createTable($this->connection, $table);
                array_push($result, ...$this->deleteOldVersions($this->connection, $table, $historySize));
            }

            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new LogRotationException(
                message:  $exception->getMessage(),
                previous: $exception,
            );
        }
        return $result;
    }

    /**
     * @param Connection $connection
     * @param string $tableName
     * @return string
     * @throws Exception
     */
    private function renameTable(Connection $connection, string $tableName): string
    {
        $newTableName = $tableName . '_' . date(self::DATE_FORMAT);
        $connection->executeStatement(
            sprintf(
                'ALTER TABLE %s RENAME TO %s;',
                $tableName,
                $newTableName,
            ),
        );
        $connection->executeStatement(
            sprintf(
                'ALTER INDEX idx_%s_level RENAME TO idx_%s_level;',
                $tableName,
                $newTableName,
            ),
        );
        $connection->executeStatement(
            sprintf(
                'ALTER INDEX idx_%s_created_at RENAME TO idx_%s_created_at;',
                $tableName,
                $newTableName,
            ),
        );
        return sprintf('Table `%s` moved to `%s`', $tableName, $newTableName);
    }

    /**
     * @param Connection $connection
     * @param string $tableName
     * @param int $historySize
     * @return array
     * @throws LogRotationException
     * @throws Exception
     */
    private function deleteOldVersions(Connection $connection, string $tableName, int $historySize): array
    {
        $result = [];
        /** Support for DBAL 2.x */
        $schemaManager = method_exists($connection, 'createSchemaManager')
            ? $connection->createSchemaManager()
            : $connection->getSchemaManager() ?? throw new LogRotationException('Unable to get SchemaManager');

        $tables = array_filter(
            $schemaManager->listTableNames(),
            static fn ($value) => str_starts_with($value, $tableName . '_'),
            ARRAY_FILTER_USE_BOTH,
        );
        rsort($tables);

        for ($i = $historySize, $tablesCount = count($tables); $i < $tablesCount; $i++) {
            $connection->executeStatement(
                sprintf(
                    'DROP TABLE IF EXISTS %s;',
                    $tables[$i],
                ),
            );
            $result[] = sprintf('Table `%s` dropped.', $tables[$i]);
        }
        return $result;
    }
}
