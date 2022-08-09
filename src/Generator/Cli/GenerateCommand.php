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
            ->addArgument('module', InputArgument::OPTIONAL, 'Module (only for presenter).')
            ->addArgument('folder', InputArgument::OPTIONAL, 'Folder path (only for presenter).')
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
            $module = $input->getArgument('module') ?? null;
            $folder = $input->getArgument('folder') ?? null;
            $type = $input->getOption('type') ?? null;
            $entity = $input->getOption('entity') ?? null;
            if ($type && $name) {
                switch(strtolower($type)) {
                    case 'database':
                    case 'd':
                        $output->writeln("Generating entity <options=bold>{$name}</>...");
                        $output->writeln("Generating facade <options=bold>{$name}Facade</>...");
                        $output->writeln("Generating repository <options=bold>{$name}Repository</>...");
                        FileGenerator::writeFile(Entity::generate($name), Repository::generate($name), Facade::generate($name));
                        break;
                    case 'entity':
                    case 'e':
                        $output->writeln("Generating entity <options=bold>{$name}</>...");
                        FileGenerator::writeFile(Entity::generate($name));
                        break;
                    case 'repository':
                    case 'r':
                        $output->writeln("Generating repository <options=bold>{$name}Repository</>...");
                        FileGenerator::writeFile(Repository::generate($name));
                        break;
                    case 'facade':
                    case 'fa':
                        $output->writeln("Generating facade <options=bold>{$name}Facade</>...");
                        FileGenerator::writeFile(Facade::generate($name));
                        break;
                    case 'presenter':
                    case 'p':
                        if (!$folder) {
                            return Command::INVALID;
                        }
                        $this->runCommand('gen:p', [
                            'command' => 'gen:p',
                            'name' => $name,
                            'module' => $module ?? 'Admin',
                            'folder' => $folder,
                        ], $output);
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
                    case 'c':
                        $this->runCommand('gen:c', [
                            'command' => 'gen:c',
                            'name' => $name,
                            '--entity' => $entity,
                        ], $output);
                        break;
                    case 'form':
                    case 'f':
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
