<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\TableCreation;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

interface TableCreatorInterface
{
    /**
     * @param string                     $name
     * @param AbstractSchemaManager|null $schemaManager
     *
     * @return string
     * @throws TableCreationException
     */
    public function create(string $name, ?AbstractSchemaManager $schemaManager = null): string;
}
