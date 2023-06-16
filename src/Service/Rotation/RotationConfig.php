<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service\Rotation;

/**
 * @readonly
 */
final class RotationConfig
{
    public function __construct(
        public int $historySize,
        public string $dateFormat,
    ) {
    }

    public function withHistorySize(int $historySize): self
    {
        return new self(
            historySize: $historySize,
            dateFormat: $this->dateFormat,
        );
    }
}
