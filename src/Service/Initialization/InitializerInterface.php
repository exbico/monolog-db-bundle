<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\Initialization;

interface InitializerInterface
{
    /**
     * @param array<string> $tables
     * @return list<string>
     * @throws LogInitializationException
     */
    public function init(array $tables): array;
}
