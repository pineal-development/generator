<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\Config\Configurator;
use Matronator\Generator\Generators\Entity;
use Matronator\Generator\Generators\Facade;
use Matronator\Generator\Generators\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

// #[AsCommand('generate', 'Generate files', ['gen'])]
class GenerateCommand extends Command
{
    use TRunCommand;

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
        $io = new SymfonyStyle($input, $output);
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
                $uiArgs = $entity ? [
                    'name' => $name,
                    '--entity' => $entity,
                ] : ['name' => $name];
                switch(strtolower($type)) {
                    case 'database':
                    case 'db':
                        $output->writeln("Generating entity <options=bold>{$name}</>...");
                        $output->writeln("Generating facade <options=bold>{$name}Facade</>...");
                        $output->writeln("Generating repository <options=bold>{$name}Repository</>...");
                        FileGenerator::writeFile([Entity::generate($name), Repository::generate($name), Facade::generate($name)]);
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
                            $io->error('Folder not specified.');
                            $io->block('<comment>You haven\'t specified a folder.</comment> Specify a folder as the third argument after <options=bold>[name]</> and <options=bold>[module]</>.', null, null, ' ', false, false);
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
                            ...$uiArgs,
                        ], $output);
                        $this->runCommand('gen:c', [
                            'command' => 'gen:c',
                            ...$uiArgs,
                        ], $output);
                        break;
                    case 'control':
                    case 'c':
                        $this->runCommand('gen:c', [
                            'command' => 'gen:c',
                            ...$uiArgs,
                        ], $output);
                        break;
                    case 'form':
                    case 'f':
                        $this->runCommand('gen:f', [
                            'command' => 'gen:f',
                            ...$uiArgs,
                        ], $output);
                        break;
                    case 'datagrid':
                    case 'grid':
                    case 'dg':
                        $this->runCommand('gen:dg', [
                            'command' => 'gen:dg',
                            ...$uiArgs,
                        ], $output);
                        break;
                    default:
                        $io->error('Invalid type.');
                        $io->block('<comment>The type you entered is not recognized.</comment> Recognized types are: <options=bold>[database, entity, repository, facade, presenter, ui, control, form]</>', null, null, ' ', false, false);
                        return Command::INVALID;
                }
            } else {
                $io->section('Matronator\'s Generator');
                $io->block('Running the interactive generator...');
                $helper = $this->getHelper('question');
                $selectType = new ChoiceQuestion(
                    '<comment><options=bold>What do you want to generate?</> (defaults to <options=bold>entity</>)</comment>',
                    ['database', 'entity', 'repository', 'facade', 'presenter', 'ui', 'control', 'form', 'datagrid'],
                    1,
                );
                $selectType->setErrorMessage('Option %s is invalid.');

                $chosenType = $helper->ask($input, $output, $selectType);

                $io->newLine();
                $io->block($chosenType . ' will be generated.', null, null, ' ', true, false);

                $nameQuestion = new Question(
                    '<comment><options=bold>Enter the name of your entity</> (without any suffixes like -Facade or -Form):</comment> ',
                    'Test',
                );
                $validateName = Validation::createCallable(new Regex([
                    'pattern' => '/^[a-zA-Z_][\w]*?$/',
                    'message' => 'Value can only contain letters, numbers and underscore [a-Z0-9_] and cannot start with a number.',
                ]));
                $nameQuestion->setValidator($validateName);

                $chosenName = $helper->ask($input, $output, $nameQuestion);

                $io->newLine();
                $io->block('Entity named ' . $chosenName . ' will be generated.', null, null, ' ', true, false);

                if (in_array($chosenType, ['ui', 'form', 'control', 'datagrid'])) {
                    $entityQuestion = new Question('<comment><options=bold>Enter the Entity to which your component(s) belong</> (or leave empty):</comment> ');
                    $entityQuestion->setValidator($validateName);

                    $chosenEntity = $helper->ask($input, $output, $entityQuestion) ?? null;

                    $io->newLine();
                    $io->block($chosenEntity ? 'Component will be generated into the <options=bold>' . $chosenEntity . '</> entity.' : 'Component will be generated without an associated entity.', null, null, ' ', true, false);

                    $this->runCommand('gen', $chosenEntity ? [
                        'command' => 'gen',
                        '--type' => $chosenType,
                        'name' => $chosenName,
                        '--entity' => $chosenEntity,
                    ] : [
                        'command' => 'gen',
                        '--type' => $chosenType,
                        'name' => $chosenName,
                    ], $output);
                    return Command::SUCCESS;
                } else if ($chosenType === 'presenter') {
                    $moduleQuestion = new Question('<comment><options=bold>Enter the module of the Presenter</> (defaults to <options=bold>Admin</>):</comment> ', 'Admin');
                    $moduleQuestion->setValidator($validateName);

                    $chosenModule = $helper->ask($input, $output, $moduleQuestion);

                    $io->newLine();
                    $io->block('Presenter will be generated into the <options=bold>' . $chosenModule . '</> module.', null, null, ' ', true, false);

                    $folderQuestion = new Question('<comment><options=bold>Enter the path to the Presenter</> from module root (if it\'s in folder <options=bold>app/modules/Admin/Test/Detail</> then you\'d type <options=bold>"Test/Detail"</>):</comment> ', 'folder');
                    $validateFolder = Validation::createCallable(new Regex([
                        'pattern' => '/^(?![\/])(?![\w\/]*[\/]$)[\w\/]*/',
                        'message' => 'Value must be a valid path without leading or trailing slashes and can only contain letters, numbers, underscore [a-Z0-9_] and slashes in between folders.',
                    ]));
                    $folderQuestion->setValidator($validateFolder);

                    $chosenFolder = $helper->ask($input, $output, $folderQuestion);

                    $io->newLine();
                    $io->block('Presenter will be located in <options=bold>' . $chosenFolder . '</>.');

                    $this->runCommand('gen:p', [
                        'command' => 'gen:p',
                        '--type' => $chosenType,
                        'name' => $chosenName,
                        'module' => $chosenModule,
                        'folder' => $chosenFolder,
                    ], $output);
                }
            }
        }

        $output->writeln('<info>All files generated!</info>');
        $io->newLine();

        return Command::SUCCESS;
    }
}
