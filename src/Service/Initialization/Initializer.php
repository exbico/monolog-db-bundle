<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\Initialization;

use Doctrine\DBAL\Connection;
use Exbico\MonologDbBundle\Service\TableCreation\TableCreatorInterface;
use Throwable;

final class Initializer implements InitializerInterface
{
    public function __construct(
        private Connection $connection,
        private TableCreatorInterface $tableCreator,
    ) {
    }

    /**
     * @param array<string> $tables
     * @return list<string>
     * @throws LogInitializationException
     */
    public function init(array $tables): array
    {
        $result = [];

        try {
            $this->connection->beginTransaction();
            foreach ($tables as $table) {
                $result[] = $this->tableCreator->create($table);
            }

            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new LogInitializationException(message: $exception->getMessage(), previous: $exception);
        }

        return $result;
    }
}
