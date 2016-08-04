<?php

namespace Antonov\Pivots\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Antonov\Pivots\ProcessHandler;
use Antonov\Pivots\Config;

class ExtractMenuCommand extends ExtractAbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $config = Config::getInstance()->getConfig();        
        $this
        // the name of the command (the part after "bin/console")
        ->setName('app:extract-menu')

        // the short description shown while running "php bin/console list"
        ->setDescription('Extract menu from page ('.implode(', ', $config->menu_entities).')')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp("This command allows you to extract the data from one page, as it could be menu links or items from a list: ". implode(', ', $config->menu_entities));

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->process->launchMenu();   
    }
}
