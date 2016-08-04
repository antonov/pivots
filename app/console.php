#!/usr/bin/env php
<?php

// set to run indefinitely if needed
set_time_limit(0);

/* Optional. It’s better to do it in the php.ini file */
date_default_timezone_set('Europe/Brussels'); 

require_once __DIR__."/../Antonov/vendor/autoload.php";
use Antonov\Pivots\ProcessHandler;
use Symfony\Component\Console\Application;
use Antonov\Pivots\Command\ExtractMenuCommand;
use Antonov\Pivots\Command\ExtractContentCommand;

$application = new Application();
$application->add(new ExtractMenuCommand());
$application->add(new ExtractContentCommand());
$application->run();