<?php

namespace Kitpages\WorkflowBundle\Proxy;

use Symfony\Component\Filesystem\Filesystem;
use ReflectionClass;

/**
 * This class is used to generate a proxy for a step command
 *
 * @example
 */
class ProxyGenerator
{
    /**
     * The class that will be turned into a proxy.
     *
     * @var string
     */
    private $class;

    /**
     * The class of the proxy.
     *
     * @var string
     */
    private $proxyClass;

    /**
     * Debug mode.
     *
     * @var bool
     */
    private $debug;

    /**
     * The main cache directory.
     *
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $proxyClassCacheFilename;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Filepath of the php Proxy Class template file.
     *
     * @var string
     */
    private $template;

    /**
     * @param string $class    The class that will proxy
     * @param bool   $debug
     * @param string $cacheDir
     */
    public function __construct($class, $debug, $cacheDir)
    {
        $this->class = $class;
        $this->debug = $debug;
        $this->cacheDir = $cacheDir;
        $this->proxyClass = '\\'.__NAMESPACE__.$this->class;
        $this->proxyClassCacheFilename = $this->getProxyCacheFilename();
        $this->fs = new Filesystem();

        $this->template = __DIR__.'/ProxyTemplate.php.tpl';
    }

    /**
     * Instantiate and returns a proxy instance.
     *
     * @param array $arguments (optionnal) The arguments to pass to the constructor of the Proxy Class if needed
     *
     * @return mixed The instanciated Proxy
     */
    public function generateProcessProxy($arguments = array())
    {
        if (!$this->isProxyLoaded()) {
            $this->loadProxyClass();
        }

        $reflect = new ReflectionClass($this->proxyClass);

        return $reflect->newInstanceArgs($arguments);
    }

    /**
     * Writes the proxy class definition into a cache file.
     *
     * @return $this
     */
    public function writeProxyClassCache()
    {
        $parameters = array(
            'proxyNameSpace' => $this->getProxyNameSpace(),
            'proxyClassName' => $this->proxyClass,
            'shortClassName' => $this->getShortClassName(),
            'originalClassName' => $this->class,
        );
        $proxyClassDefinition = $this->render($this->template, $parameters);
        $this->fs->dumpFile($this->proxyClassCacheFilename, $proxyClassDefinition);

        return $this;
    }

    /**
     * Loads the proxy class for usage.
     *
     * @return self
     */
    public function loadProxyClass()
    {
        if (!$this->cacheFileExists() || $this->debug) {
            $this->writeProxyClassCache();
        }

        if(!$this->isProxyLoaded()) {
            require $this->proxyClassCacheFilename;
        }

        return $this;
    }

    /**
     * Returns the filepath to the Proxy cache file.
     *
     * @return string
     */
    public function getProxyCacheFilename()
    {
        return sprintf(
            '%s/kitpages_proxy/%s_%s.php',
            $this->cacheDir,
            md5($this->proxyClass),
            $this->getShortClassName()
        );
    }

    /**
     * Getter de proxyClass
     *
     * @return string
     */
    public function getProxyClass()
    {
        return $this->proxyClass;
    }

    /**
     * Returns true if the Proxy cache file exists.
     *
     * @return bool
     */
    private function cacheFileExists()
    {
        return $this->fs->exists($this->getProxyCacheFilename());
    }

    /**
     * Returns true if the ProxyClass has been loaded.
     *
     * @return bool
     */
    private function isProxyLoaded()
    {
        return class_exists($this->proxyClass);
    }

    /**
     * @param string $proxyTemplateFile Filepath of the Proxy template
     * @param array  $parameters
     *
     * @return string The php code of the proxy class.
     */
    private function render($proxyTemplateFile, $parameters = array())
    {
        $proxyTemplateContent = file_get_contents($proxyTemplateFile);

        foreach ($parameters as $key => $val) {
            $proxyTemplateContent = str_replace("<<$key>>", $val, $proxyTemplateContent);
        }

        return $proxyTemplateContent;
    }

    /**
     * @return string
     */
    private function getProxyNameSpace()
    {
        $proxyNameSpaceTab = explode('\\', trim($this->proxyClass, '\\'));
        array_pop($proxyNameSpaceTab);

        return implode('\\', $proxyNameSpaceTab);
    }

    /**
     * @return string
     */
    private function getShortClassName()
    {
        $proxyNameSpaceTab = explode('\\', $this->class);

        return array_pop($proxyNameSpaceTab);
    }
}
