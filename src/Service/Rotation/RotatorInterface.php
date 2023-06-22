<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\Rotation;

interface RotatorInterface
{
    /**
     * @param array<string> $tables
     * @param RotationConfig $config
     * @return list<string>
     * @throws LogRotationException
     */
    public function rotate(array $tables, RotationConfig $config): array;
}
