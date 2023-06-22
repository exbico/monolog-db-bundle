<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\TableCreation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Throwable;

final class TableCreator implements TableCreatorInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param string $name
     * @return string
     * @throws TableCreationException
     */
    public function create(string $name): string
    {
        try {
            $schemaManager = $this->getSchemaManager();

            $table = $this->getTableInstance($name);

            if (!$schemaManager->tablesExist($name)) {
                $schemaManager->createTable($table);
            }
        } catch (Throwable $exception) {
            throw new TableCreationException(message: 'Failed to create table:', previous: $exception);
        }
        return sprintf('Table `%s` has been created.', $name);
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

    /**
     * @param string $name
     * @return Table
     * @throws Exception
     * @throws SchemaException
     */
    private function getTableInstance(string $name): Table
    {
        return new Table(
            name   : $name,
            columns: [
                         new Column(
                             name   : 'id',
                             type   : new IntegerType(),
                             options: ['unsigned' => true, 'autoincrement' => true],
                         ),
                         new Column(
                             name   : 'level',
                             type   : new StringType(),
                             options: ['length' => 255, 'notnull' => true],
                         ),
                         new Column(
                             name   : 'message',
                             type   : new TextType(),
                             options: ['notnull' => true],
                         ),
                         new Column(
                             name   : 'datetime',
                             type   : new DateTimeImmutableType(),
                             options: ['notnull' => true],
                         ),
                         (new Column(
                             name   : 'context',
                             type   : new JsonType(),
                             options: ['notnull' => false],
                         ))->setPlatformOptions(['jsonb' => true]),
                         (new Column(
                             name   : 'extra',
                             type   : new JsonType(),
                             options: ['notnull' => false],
                         ))->setPlatformOptions(['jsonb' => true]),
                     ],
            indexes: [
                         new Index(name: 'idx_' . $name . '_level', columns: ['level']),
                         new Index(name: 'idx_' . $name . '_datetime', columns: ['datetime']),
                     ],
        );
    }
}
