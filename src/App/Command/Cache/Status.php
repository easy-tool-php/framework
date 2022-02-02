<?php

namespace EasyTool\Framework\App\Command\Cache;

use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Command\AbstractCommand;

class Status extends AbstractCommand
{
    private const MIN_LEN = 38;
    private const PADDING = 2;

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
    protected function doExecution(): void
    {
        $length = 0;
        foreach ($this->cacheManger->getRegisteredCaches() as $cacheName => $status) {
            $length = max($length, strlen($cacheName));
        }
        $length = max(self::MIN_LEN, $length + self::PADDING);

        $msg = [
            str_repeat('-', $length + 12),
            sprintf(
                '| %s%s |',
                str_pad('Cache', $length, ' ', STR_PAD_RIGHT),
                str_pad('Status', 8, ' ', STR_PAD_LEFT)
            ),
            sprintf('|%s|', str_repeat('-', $length + 10))
        ];
        foreach ($this->cacheManger->getRegisteredCaches() as $cacheName => $status) {
            $msg[] = sprintf(
                '| %s%s |',
                str_pad($cacheName, $length, ' ', STR_PAD_RIGHT),
                $status ? ' <fg=green>enabled</>' : '<fg=red>disabled</>'
            );
        }
        $msg[] = str_repeat('-', $length + 12);

        $this->output->writeln($msg);
    }
}
