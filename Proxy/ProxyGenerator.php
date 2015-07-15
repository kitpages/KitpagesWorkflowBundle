<?php

namespace Kitpages\WorkflowBundle\Proxy;

use Symfony\Component\Filesystem\Filesystem;

/**
 * This class is used to generate a proxy for a workflow.
 *
 * @example
 */
class ProxyGenerator
{
    /**
     * The class of the workflow.
     *
     * @var string
     */
    protected $workflowClass;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debug;

    /**
     * The main cache directory.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $proxyClassCacheFilename;

    /**
     * @param      $workflowClass
     * @param bool $debug
     * @param      $cacheDir
     */
    public function __construct($workflowClass, $debug, $cacheDir)
    {
        $this->workflowClass = $workflowClass;
        $this->debug = $debug;
        $this->cacheDir = $cacheDir;
        $this->proxyClassCacheFilename = $this->getProxyCacheFilename($cacheDir);
    }

    /**
     * @param string $cacheDir
     *
     * @return string
     */
    public function getProxyCacheFilename($cacheDir)
    {
        return $cacheDir.'/kitpages_workflow/WorkflowProxy.php';
    }

    /**
     * @param string $originalClassName (optionnel) Can be specified for backward compatibility.
     *
     * @return mixed
     */
    public function generateProcessProxy($originalClassName = null)
    {
        $originalClassName = $originalClassName ?: $this->workflowClass;

        $className = $this->generateProcessProxyClass($originalClassName);
        $proxy = new $className();

        return $proxy;
    }

    public function generateProcessProxyClass($originalClassName = null)
    {
        $originalClassName = $originalClassName ?: $this->workflowClass;
        $proxyClassName = $this->getProxyClassName($originalClassName);
        if (class_exists($proxyClassName)) {
            return $proxyClassName;
        }

        $parameters = array(
            'proxyNameSpace' => $this->getProxyNameSpace($originalClassName),
            'proxyClassName' => $this->getProxyClassName($originalClassName),
            'shortClassName' => $this->getShortClassName($originalClassName),
            'originalClassName' => $originalClassName,
        );
        $templateFile = __DIR__.'/ProxyTemplate.php.tpl';

        return $this->generateProxyClass($originalClassName, $templateFile, $parameters);
    }

    public function getProxyNameSpace($originalClassName)
    {
        $proxyNameSpaceTab = explode('\\', trim($this->getProxyClassName($originalClassName), '\\'));
        array_pop($proxyNameSpaceTab);

        return implode('\\', $proxyNameSpaceTab);
    }

    public function getProxyClassName($originalClassName)
    {
        return '\\'.__NAMESPACE__.$originalClassName;
    }

    public function getShortClassName($originalClassName)
    {
        $proxyNameSpaceTab = explode('\\', $originalClassName);

        return array_pop($proxyNameSpaceTab);
    }

    public function generateProxyClass($originalClassName, $templateFile, $parameters = array())
    {
        $fs = new Filesystem();
        $proxyClassName = $this->getProxyClassName($originalClassName);
        if (class_exists($proxyClassName)) {
            return $proxyClassName;
        }

        if (!$fs->exists($this->proxyClassCacheFilename) || $this->debug) {
            $proxyTemplateContent = file_get_contents($templateFile);
            $proxyClassDefinition = $this->replaceInTemplate($proxyTemplateContent, $parameters);
            $fs->dumpFile($this->proxyClassCacheFilename, $proxyClassDefinition);
        }

        require $this->proxyClassCacheFilename;

        return $proxyClassName;
    }

    public function replaceInTemplate($proxyTemplateContent, $parameters = array())
    {
        foreach ($parameters as $key => $val) {
            $proxyTemplateContent = str_replace("<<$key>>", $val, $proxyTemplateContent);
        }

        return $proxyTemplateContent;
    }
}
