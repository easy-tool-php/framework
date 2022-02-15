<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Command\Setup;

use EasyTool\Framework\App\Command\AbstractCommand;
use EasyTool\Framework\App\Setup\Upgrade as Processor;

class Upgrade extends AbstractCommand
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
    protected function doExecution(): void
    {
        $this->output->writeln('<info>Preparing for upgrade.</info>');
        $this->processor->prepareForUpgrade();
        $setups = $this->processor->collectSetups();

        if (count($setups) > 0) {
            $this->output->writeln('<info>Starting upgrade:</info>');
            foreach ($setups as $setup) {
                $this->output->write(sprintf('Processing `%s`...', $setup));
                $this->processor->process($setup);
                $this->output->writeln(' Done');
            }
        }

        $this->output->writeln('<info>All setups were processed.</info>');
    }
}
