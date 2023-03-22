<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\DataGridControl;
use Matronator\Generator\Generators\FormControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:control', 'Generates a Control file', ['gen:control'])]
class GenerateDataGridCommand extends Command
{
    protected static $defaultName = 'generate:datagrid';
    protected static $defaultDescription = 'Generates a DataGrid file.';

    public function configure(): void
    {
        $this->setAliases(['gen:grid', 'gen:dg', 'gen:datagrid']);

        $this->addArgument('name', InputArgument::REQUIRED, 'DataGrid name without the `DataGrid` suffix which will be added automatically.')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the grid belongs.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $entity = $input->getOption('entity') ?? null;

        if (!$entity) {
            $output->writeln('<fg=red>Entity is required!</>');

            return self::FAILURE;
        }

        $output->writeln("Generating <options=bold>{$name}DataGrid</> to entity {$entity}...");
        FileGenerator::writeFile(DataGridControl::generate($name, $entity));

        DataGridControl::generateTemplate($name, $entity);

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }
}
