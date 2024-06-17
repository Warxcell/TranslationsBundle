<?php

declare(strict_types=1);

namespace Arxy\TranslationsBundle;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheFlag
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private string $key = 'translations'
    ) {
    }

    public function getVersion(): CacheItemInterface
    {
        $item = $this->cache->getItem($this->key);
        if (!$item->isHit()) {
            $item->set(0);
        }

        return $item;
    }

    public function increment(CacheItemInterface $item): void
    {
        $item->set($item->get() + 1);
        $this->cache->save($item);
    }
}
