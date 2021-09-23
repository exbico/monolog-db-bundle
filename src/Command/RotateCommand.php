<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Command;

use Exbico\MonologDbBundle\Service\RotatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class RotateCommand extends Command
{
    private const HISTORY_SIZE_KEY = 'history_size';

    protected static $defaultName = 'log:rotate';

    public function __construct(private RotatorInterface $rotator)
    {
        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Rotates log tables.')
            ->setHelp('This command allows you to rotate log tables...')
            ->addArgument(
                name:        self::HISTORY_SIZE_KEY,
                mode:        InputArgument::OPTIONAL,
                description: 'Number of versions to keep.',
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $historySize = $input->getArgument(self::HISTORY_SIZE_KEY);
        try {
            if ($historySize !== null) {
                if (!is_numeric($historySize)) {
                    throw new InvalidArgumentException('HistorySize argument should be an integer.');
                }
                $historySize = (int)$historySize;
            }
            foreach ($this->rotator->rotate($historySize) as $result) {
                $io->success($result);
            }
            $io->success('Log rotation successfully completed.');
        } catch (Throwable $exception) {
            $io->error('Failed to rotate logs: ' . $exception->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
