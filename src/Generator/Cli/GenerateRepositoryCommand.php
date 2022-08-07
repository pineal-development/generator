<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:repository', 'Generates a Repository file', ['gen:repository'])]
class GenerateRepositoryCommand extends Command
{
    protected static $defaultName = 'generate:repository';
    protected static $defaultDescription = 'Generates a Repository file.';

    public function configure(): void
    {
        $this->setAliases(['gen:r', 'gen:repo']);
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln("Generating <options=bold>{$name}Repository</>...");

        FileGenerator::writeFile(Repository::generate($name));

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }
}
