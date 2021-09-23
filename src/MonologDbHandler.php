<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Exception;
use JsonException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

use function is_array;

final class MonologDbHandler extends AbstractProcessingHandler
{
    public const DEFAULT_CATEGORY    = 'default';
    public const DEPRECATED_CATEGORY = 'deprecated';

    private UuidInterface $id;
    private bool $initialized = false;

    private Statement $queryInfo;
    private Statement $queryError;

    public function __construct(private Connection $connection)
    {
        parent::__construct(Logger::INFO);
        $this->id = Uuid::uuid4();
    }

    protected function write(array $record): void
    {
        $level = $this->isAboutDeprecation($this->getRecordMessage($record))
            ? Logger::WARNING
            : $this->getRecordLevel($record);
        if (!$this->initialized) {
            try {
                $this->initialize();
            } catch (Throwable) {
                return;
            }
        }

        $logQuery = $this->getLogQuery($level);
        try {
            $logQuery->executeQuery(
                [
                    'level'    => Logger::getLevelName($level),
                    'message'  => $this->getRecordMessage($record),
                    'context'  => $this->getRecordContext($record),
                ],
            );
        } catch (Throwable) {
        }
    }

    /**
     * @throws Exception
     */
    private function initialize(): void
    {
        $this->queryInfo = $this->connection->prepare(
            'INSERT INTO log_info (level, message, context) VALUES (:level, :message, :context)',
        );
        $this->queryError = $this->connection->prepare(
            'INSERT INTO log_error (level, message, context) VALUES (:level, :message, :context)',
        );
        $this->initialized = true;
    }

    protected function getLogQuery(int $level): Statement
    {
        return match ($level) {
            Logger::DEBUG, Logger::INFO, Logger::NOTICE => $this->queryInfo,
            default => $this->queryError,
        };
    }

    private function getRecordLevel(array $record): int
    {
        return (int)($record['level'] ?? Logger::ERROR);
    }

    private function getRecordMessage(array $record): ?string
    {
        return $record['message'] ?? null;
    }

    /**
     * @param array $record
     * @return string
     * @throws JsonException
     */
    private function getRecordContext(array $record): string
    {
        $context = $record['context'] ?? [];
        if (!is_array($context)) {
            $context = [];
        }

        if (!isset($context['category'])) {
            $context['category'] = $this->isAboutDeprecation($this->getRecordMessage($record))
                ? self::DEPRECATED_CATEGORY
                : self::DEFAULT_CATEGORY;
        }
        if (
            isset($context['exception'])
            && $context['exception'] instanceof Throwable
        ) {
            $context['origin']['message'] = $context['exception']->getMessage();
            $context['origin']['class'] = $context['exception']::class;
            $context['origin']['trace'] = $context['exception']->getTraceAsString();
            unset($context['exception']);
        }

        $context['id'] = $this->id;
        $context['memory_usage'] = memory_get_usage();
        $context['peak_memory_usage'] = memory_get_peak_usage();

        return json_encode($context, JSON_THROW_ON_ERROR);
    }

    private function isAboutDeprecation(?string $message): bool
    {
        return $message !== null && stripos($message, 'deprecat') !== false;
    }
}
