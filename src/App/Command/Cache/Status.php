<?php

namespace EasyTool\Framework\App\Command\Cache;

use EasyTool\Framework\App\Cache\Manager as CacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command
{
    private CacheManager $cacheManger;

    public function __construct(
        CacheManager $cacheManger,
        string $name = null
    ) {
        $this->cacheManger = $cacheManger;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('cache:status')
            ->setDescription('Check cache status.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
