<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Symfony\Component\Console\Application as ConsoleApplication;

class Application
{
    public ConsoleApplication $app;

    public function __construct()
    {
        $this->app = new ConsoleApplication('MTRGen', '1.4.1');
        $this->app->addCommands([
            new GenerateCommand(),
            new GenerateEntityCommand(),
            new GenerateFacadeCommand(),
            new GenerateRepositoryCommand(),
            new GenerateFormCommand(),
            new GenerateControlCommand(),
            new GeneratePresenterCommand(),
        ]);
        $this->app->setDefaultCommand('generate');
    }
}
