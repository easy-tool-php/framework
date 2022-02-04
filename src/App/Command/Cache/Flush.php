<?php

namespace EasyTool\Framework\App\Command\Cache;

use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Command\AbstractCommand;

class Flush extends AbstractCommand
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
        $this->setName('cache:flush')
            ->setDescription('Flush all caches.');
    }

    /**
     * @inheritDoc
     */
    protected function doExecution(): void
    {
        $this->cacheManger->flushCache();
        $this->output->writeln('<info>All caches flushed.</info>');
    }
}
