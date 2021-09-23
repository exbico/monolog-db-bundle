<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Service;

use App\Infrastructure\Log\LogRotationException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception;

interface RotatorInterface
{
    /**
     * @param int|null $historySize
     * @return array
     * @throws ConnectionException
     * @throws Exception
     * @throws LogRotationException
     */
    public function rotate(?int $historySize): array;
}
