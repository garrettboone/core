<?php

declare(strict_types=1);

namespace Bolt\Menu;

use Bolt\Collection\DeepCollection;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class CachedFrontendMenuBuilder
{
    /** @var CacheInterface */
    private $cache;

    /** @var FrontendMenuBuilder */
    private $menuBuilder;

    /** @var Stopwatch */
    private $stopwatch;

    public function __construct(CacheInterface $cache, FrontendMenuBuilder $menuBuilder, Stopwatch $stopwatch)
    {
        $this->cache = $cache;
        $this->menuBuilder = $menuBuilder;
        $this->stopwatch = $stopwatch;
    }

    public function getMenu(?string $name = ''): ?DeepCollection
    {
        $key = 'frontendmenu_' . ($name ?: 'default');

        if ($this->cache->has($key)) {
            $menu = $this->cache->get($key);
        } else {
            $this->stopwatch->start('bolt.frontendMenu');

            $menu = $this->menuBuilder->getMenu($name);

            $this->cache->set($key, $menu);

            $this->stopwatch->stop('bolt.frontendMenu');
        }

        return $menu;
    }

    public function getMenuJson(?string $name = '', bool $jsonPrettyPrint = false): string
    {
        $menu = $this->getMenu($name);
        $options = $jsonPrettyPrint ? JSON_PRETTY_PRINT : 0;

        return json_encode($menu, $options);
    }
}
