<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\Presenter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:presenter', 'Generates a Presenter file', ['gen:p'])]
class GeneratePresenterCommand extends Command
{
    protected static $defaultName = 'generate:presenter';
    protected static $defaultDescription = 'Generates a Presenter file with templates.';

    public function configure(): void
    {
        $this->setAliases(['gen:p', 'gen:presenter']);
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
        $this->addArgument('module', InputArgument::REQUIRED, 'Module (eg.: Front, Admin, ...)');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder path (eg.: client/detail, client, ...)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = strtolower($input->getArgument('module'));
        $folder = $input->getArgument('folder');

        $output->writeln("Generating <options=bold>BasePresenter</> in {$folder}...");

        Presenter::createFolder($folder, $module);
        FileGenerator::writeFile(Presenter::generateBase($folder, $module));

        $output->writeln("Generating <options=bold>{$name}Presenter</>...");

        FileGenerator::writeFile(Presenter::generate($name, $folder, $module));

        $output->writeln("Generating template <options=bold>default.latte</>...");

        Presenter::generateTemplate($folder, $module);

        $output->writeln('<info>All done!</info>');

        return self::SUCCESS;
    }
}
