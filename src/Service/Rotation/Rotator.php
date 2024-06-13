<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\Rotation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Exbico\MonologDbBundle\Service\TableCreation\TableCreatorInterface;
use Throwable;

use function count;

final class Rotator implements RotatorInterface
{
    public function __construct(
        private Connection $connection,
        private TableCreatorInterface $tableCreator,
    ) {
    }

    /**
     * @param array<string> $tables
     * @param RotationConfig $config
     * @return list<string>
     * @throws LogRotationException
     */
    public function rotate(array $tables, RotationConfig $config): array
    {
        $result = [];

        try {
            $this->connection->beginTransaction();
            $schemaManager = $this->getSchemaManager();
            foreach ($tables as $table) {
                $result[] = $this->renameTable($table, $config, $schemaManager);
                $result[] = $this->tableCreator->create($table, $schemaManager);
                array_push($result, ...$this->deleteOldVersions($table, $config));
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
     * @param string $tableName
     * @param AbstractSchemaManager $schemaManager
     * @return string
     * @throws Exception
     */
    private function renameTable(
        string $tableName,
        RotationConfig $config,
        AbstractSchemaManager $schemaManager,
    ): string
    {
        $newTableName = $tableName . '_' . date($config->dateFormat);

        $schemaManager->renameTable($tableName, $newTableName);

        $this->connection->executeStatement(
            sprintf(
                'ALTER INDEX idx_%s_level RENAME TO idx_%s_level;',
                $tableName,
                $newTableName,
            ),
        );
        $this->connection->executeStatement(
            sprintf(
                'ALTER INDEX idx_%s_datetime RENAME TO idx_%s_datetime;',
                $tableName,
                $newTableName,
            ),
        );
        return sprintf('Table `%s` has been moved to `%s`', $tableName, $newTableName);
    }

    /**
     * @param string $tableName
     * @param RotationConfig $config
     * @return list<string>
     * @throws Exception
     */
    private function deleteOldVersions(string $tableName, RotationConfig $config): array
    {
        $result = [];
        $schemaManager = $this->getSchemaManager();

        $tables = array_filter(
            $schemaManager->listTableNames(),
            static fn ($value) => str_starts_with($value, $tableName . '_'),
            ARRAY_FILTER_USE_BOTH,
        );
        rsort($tables);

        for ($i = $config->historySize, $tablesCount = count($tables); $i < $tablesCount; $i++) {
            $schemaManager->dropTable($tables[$i]);
            $result[] = sprintf('Table `%s` has been dropped.', $tables[$i]);
        }
        return $result;
    }

    /**
     * @return AbstractSchemaManager
     * @throws Exception
     */
    private function getSchemaManager(): AbstractSchemaManager
    {
        /** Support for DBAL 2.x */
        return method_exists($this->connection, 'createSchemaManager')
            ? $this->connection->createSchemaManager()
            : $this->connection->getSchemaManager();
    }
}
