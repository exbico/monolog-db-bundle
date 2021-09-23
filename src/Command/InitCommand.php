<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\Command;

use Exbico\MonologDbBundle\Service\InitializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class InitCommand extends Command
{
    protected static $defaultName = 'log:init';

    public function __construct(private InitializerInterface $initializer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Initializes log tables.')
            ->setHelp('This command allows you to initialize log tables...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            foreach ($this->initializer->init() as $result) {
                $io->success($result);
            }
            $io->success('Log initialization successfully completed.');
        } catch (Throwable $exception) {
            $io->error('Failed to initialize logs: ' . $exception->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
