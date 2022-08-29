<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

trait TRunCommand
{
    private function runCommand(string $name, array $parameters, OutputInterface $output)
    {
        return $this->getApplication()
            ->find($name)
            ->run(new ArrayInput($parameters), $output);
    }
}
