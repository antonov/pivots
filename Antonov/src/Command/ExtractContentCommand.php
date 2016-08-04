<?php

namespace Antonov\Pivots\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Antonov\Pivots\ProcessHandler;
use Antonov\Pivots\Config;

class ExtractContentCommand extends ExtractAbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $config = Config::getInstance()->getConfig();        
        $this
        // the name of the command (the part aftesitemapr "bin/console")
        ->setName('app:extract-content')

        // the short description shown while running "php bin/console list"
        ->setDescription('Extract content from sitemap ('.implode(', ', $config->multi_page_entities).')')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp("This command allows you to extract content from the website, the page parameter usually is a sitemap: ". implode(', ', $config->multi_page_entities));

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->process->launch();   
    }
}
