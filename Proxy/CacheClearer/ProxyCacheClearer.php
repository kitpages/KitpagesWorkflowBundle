<?php

namespace Kitpages\WorkflowBundle\Proxy\CacheClearer;

use Kitpages\WorkflowBundle\Proxy\ProxyGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Removes the proxy cache file when the cache is cleared.
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
class ProxyCacheClearer implements CacheClearerInterface
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
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory.
     */
    public function clear($cacheDir)
    {
        $fs = new Filesystem();
        $cacheFile = $this->proxyGenerator->getProxyCacheFilename($cacheDir);
        if ($fs->exists($cacheFile)) {
            $fs->remove($cacheFile);
        }
    }
}
