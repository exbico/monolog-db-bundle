<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle;

use Exbico\MonologDbBundle\DependencyInjection\ConnectionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ExbicoMonologDbBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConnectionPass());
    }
}
