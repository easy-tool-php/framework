<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use EasyTool\Framework\App\Cache\Adapter\Filesystem\Options;
use Exception;
use GlobIterator;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\Adapter\Filesystem\Exception\MetadataException;
use Laminas\Cache\Storage\Adapter\Filesystem\LocalFilesystemInteraction;
use Laminas\Cache\Storage\Capabilities;
use Laminas\Cache\Storage\FlushableInterface;
use stdClass;

/**
 * This is a non-standard but quick implement for PSR-6
 */
class Filesystem extends AbstractAdapter implements FlushableInterface
{
    protected LocalFilesystemInteraction $filesystem;

    public function __construct(
        LocalFilesystemInteraction $filesystem,
        $options = null
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($options);
    }

    /**
     * Returns absolute filepath with specified key
     */
    protected function getFilename(string $normalizedKey): string
    {
        return $this->options->getCacheDir() . '/' . base64_encode($normalizedKey);
    }

    /**
     * Get file contents with specified absolute path
     */
    protected function getFileContents($filepath, $lock = false, $block = false, &$wouldBlock = null)
    {
        return $this->filesystem->read($filepath, $lock, $block, $wouldBlock);
    }

    /**
     * Put file contents with specified absolute path
     */
    protected function putFileContents(
        $filepath,
        $contents,
        $umask = null,
        $permissions = null,
        $lock = false,
        $block = false,
        &$wouldBlock = null
    ): bool {
        return $this->filesystem->write(
            $filepath,
            $contents,
            $umask,
            $permissions,
            $lock,
            $block,
            $wouldBlock
        );
    }

    /**
     * @inheritDoc
     */
    protected function internalHasItem(&$normalizedKey): bool
    {
        $filepath = $this->getFilename($normalizedKey);
        if (!$this->filesystem->exists($filepath)) {
            return false;
        }
        if (
            ($ttl = $this->getOptions()->getTtl())
            && (time() >= $this->filesystem->lastModifiedTime($filepath) + $ttl)
        ) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function internalGetItem(&$normalizedKey, &$success = null, &$casToken = null)
    {
        if (!$this->internalHasItem($normalizedKey)) {
            return null;
        }
        try {
            $filepath = $this->getFilename($normalizedKey);
            $data = $this->getFileContents($filepath);
            if ($casToken) {
                try {
                    $casToken = $this->filesystem->lastModifiedTime($filepath) . $this->filesystem->filesize($filepath);
                } catch (MetadataException $exception) {
                    $casToken = '';
                }
            }
            $success = true;
            return $data;
        } catch (Exception $e) {
            $success = false;
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    protected function internalSetItem(&$normalizedKey, &$value): bool
    {
        return $this->putFileContents($this->getFilename($normalizedKey), $value);
    }

    /**
     * @inheritDoc
     */
    protected function internalRemoveItem(&$normalizedKey): bool
    {
        $filepath = $this->getFilename($normalizedKey);
        return $this->filesystem->exists($filepath) ? $this->filesystem->delete($filepath) : false;
    }

    /**
     * @inheritDoc
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $this->capabilityMarker = new stdClass();
            $this->capabilities = new Capabilities(
                $this,
                $this->capabilityMarker,
                [
                    'maxTtl' => 0,
                    'minTtl' => 1,
                    'staticTtl' => true
                ]
            );
        }
        return $this->capabilities;
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        if (is_dir(($dir = $this->options->getCacheDir()))) {
            $iterator = new GlobIterator($dir . '/*', GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_PATHNAME);
            while ($iterator->valid()) {
                $this->filesystem->delete($iterator->current());
                $iterator->next();
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setOptions($options)
    {
        if (!$options instanceof Options) {
            $options = new Options($options);
        }
        return parent::setOptions($options);
    }
}
