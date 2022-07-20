<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\Config\Configurator;
use Matronator\Generator\Entity;
use Matronator\Generator\Facade;
use Matronator\Generator\FileGenerator;
use Matronator\Generator\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate', 'Generate files', ['gen'])]
class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';
    protected static $defaultDescription = 'Generates files';

    protected function configure(): void
    {
        $this->setAliases(['gen']);

        $this->setHelp('Generate files...')
            ->addArgument('name', InputArgument::OPTIONAL, 'Class name.')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'File type', 'database')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the file belongs')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');
        if ($configPath) {
            $config = new Configurator($configPath);
            $output->writeln('Generating from config: ' . $configPath . '...');
            if (isset($config->model) && $config->model) {
                $modelFiles = $config->generateModel($config->model);
                FileGenerator::writeFile($modelFiles);
            }
            if (isset($config->ui) && $config->ui) {
                $uiFiles = $config->generateUI($config->ui);
                FileGenerator::writeFile($uiFiles);
            }
        } else {
            $name = $input->getArgument('name') ?? null;
            $type = $input->getOption('type') ?? null;
            $entity = $input->getOption('entity') ?? null;
            if ($type && $name) {
                switch(strtolower($type)) {
                    case 'database':
                        FileGenerator::writeFile(Entity::generate($name), Repository::generate($name), Facade::generate($name));
                        break;
                    case 'entity':
                        FileGenerator::writeFile(Entity::generate($name));
                        break;
                    case 'repository':
                        FileGenerator::writeFile(Repository::generate($name));
                        break;
                    case 'facade':
                        FileGenerator::writeFile(Facade::generate($name));
                        break;
                    case 'ui':
                        $this->runCommand('gen:f', [
                            'command' => 'gen:f',
                            'name' => $name,
                            '--entity' => $entity,
                        ], $output);
                        $this->runCommand('gen:c', [
                            'command' => 'gen:c',
                            'name' => $name,
                            '--entity' => $entity,
                        ], $output);
                        break;
                    case 'control':
                        $this->runCommand('gen:c', [
                            'command' => 'gen:c',
                            'name' => $name,
                            '--entity' => $entity,
                        ], $output);
                        break;
                    case 'form':
                        $this->runCommand('gen:f', [
                            'command' => 'gen:f',
                            'name' => $name,
                            '--entity' => $entity,
                        ], $output);
                        break;
                }
            } else {
                return Command::INVALID;
            }
        }

        $output->writeln('<fg=green>All files generated!</>');

        return Command::SUCCESS;
    }

    private function runCommand(string $name, array $parameters, OutputInterface $output)
    {
        return $this->getApplication()
            ->find($name)
            ->run(new ArrayInput($parameters), $output);
    }
}
