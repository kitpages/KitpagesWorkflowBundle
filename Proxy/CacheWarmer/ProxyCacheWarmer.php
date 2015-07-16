<?php

namespace Kitpages\WorkflowBundle\Proxy\CacheWarmer;

use Kitpages\WorkflowBundle\Proxy\ProxyGenerator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Removes the proxy cache file when the cache is cleared.
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
class ProxyCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ProxyGenerator
     */
    private $proxyGenerator;

    /**
     * ProxyCacheClearer constructor.
     *
     * @param ProxyGenerator $proxyGenerator
     */
    public function __construct(ProxyGenerator $proxyGenerator)
    {
        $this->proxyGenerator = $proxyGenerator;
    }

    /**
     * We need the workflow proxies in the cache.
     *
     * @return bool false
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Writes the workflow proxy cache file.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->proxyGenerator->writeProxyClassCache();
    }
}
