<?php

require "Antonov/vendor/autoload.php";
use Antonov\Pivots\ProcessHandler;
$process = new ProcessHandler('/agriculture/organic/sitemap/index_en.htm', 'organic_pages');
$process->launch();

//$process = new ProcessHandler('/agriculture/organic/index_en.htm', 'menu');
//$process->launchMenu();
