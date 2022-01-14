<?php

namespace EasyTool\Framework\App\Command;

use EasyTool\Framework\App\Setup\Upgrade as Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
    private Processor $processor;

    public function __construct(
        Processor $processor,
        string $name = null
    ) {
        $this->processor = $processor;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('setup:upgrade')
            ->setDescription('Check all enabled modules for database upgrade');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processor->process();
        return 0;
    }
}
