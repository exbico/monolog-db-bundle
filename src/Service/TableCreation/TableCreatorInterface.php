<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\TableCreation;

interface TableCreatorInterface
{
    /**
     * @param string $name
     * @return string
     * @throws TableCreationException
     */
    public function create(string $name): string;
}
