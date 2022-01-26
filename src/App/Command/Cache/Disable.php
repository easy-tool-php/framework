<?php

namespace EasyTool\Framework\App\Command\Cache;

use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

class Disable extends AbstractCommand
{
    private const ARG_NAME = 'name';

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
        $this->setName('cache:disable')
            ->setDescription('Disable specified cache.')
            ->setDefinition(
                [new InputArgument(self::ARG_NAME, InputArgument::REQUIRED, 'Cache name')]
            );
    }

    /**
     * @inheritDoc
     */
    protected function doExecution(): void
    {
        $cacheName = $this->input->getArgument(self::ARG_NAME);
        $this->cacheManger->setStatus($cacheName, false);
        $this->output->writeln(sprintf('<info>Cache `%s` is disabled.</info>', $cacheName));
    }
}
