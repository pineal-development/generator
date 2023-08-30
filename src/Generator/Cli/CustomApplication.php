<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Symfony\Component\Console\Application;

class CustomApplication extends Application
{
    
    private static $logo = <<<LOGO

    ┳┳┓                ╹   ┏┓               
    ┃┃┃┏┓╋┏┓┏┓┏┓┏┓╋┏┓┏┓ ┏  ┃┓┏┓┏┓┏┓┏┓┏┓╋┏┓┏┓
    ┛ ┗┗┻┗┛ ┗┛┛┗┗┻┗┗┛┛  ┛  ┗┛┗ ┛┗┗ ┛ ┗┻┗┗┛┛ 

    
LOGO;

    private static $name = 'MTRGen';

    public function __construct(?string $name, $version = 'UNKNOWN')
    {
        if ($name !== null) {
            static::$name = $name;
        }

        $this->setName(static::$name);
        $this->setVersion($version);

        parent::__construct($name, $version);
    }

    // public function getHelp(): string
    // {
    //     return static::$logo . parent::getHelp();
    // }

    public function getVersion(): string
    {
        return 'version ' . parent::getVersion();
    }

    public function getLongVersion()
    {
        return '<info>' . static::$logo . '</info>' . parent::getLongVersion();
    }
}
