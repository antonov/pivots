<?php

namespace Antonov\Pivots\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Antonov\Pivots\ProcessHandler;

abstract class ExtractAbstractCommand extends Command
{
    protected $process;
    protected function configure()
    {
        $this
         // configure an argument
        ->addArgument('page', InputArgument::REQUIRED, 'The page used to extract')
        ->addArgument('entity', InputArgument::REQUIRED, 'The entity to extract from the page')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Extracting entities for '.$input->getArgument('entity'),
            '==============================================',
            '',
        ]);
        
        $this->process = new ProcessHandler($input->getArgument('page'), $input->getArgument('entity'));
        
    }
}
