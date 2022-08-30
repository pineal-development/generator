<?php

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\Generator;

require __DIR__ . '/../vendor/autoload.php';

FileGenerator::writeFile(Generator::parseFile(__DIR__.'/../templates/Entity.yaml', ['name' => 'Test', 'entity' => 'Entity']));
