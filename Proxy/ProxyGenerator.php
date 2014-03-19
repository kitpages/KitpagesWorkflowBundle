<?php
namespace Kitpages\WorkflowBundle\Proxy;

/**
 * This class is used to generate a proxy for a workflow
 *
 * @example
 */
class ProxyGenerator
{
    public function __construct()
    {
    }

    public function generateProcessProxy($originalClassName)
    {
        $className = $this->generateProcessProxyClass($originalClassName);
        $proxy = new $className();

        return $proxy;
    }

    public function generateProcessProxyClass($originalClassName)
    {
        $proxyClassName = $this->getProxyClassName($originalClassName);
        if (class_exists($proxyClassName)) {
            return $proxyClassName;
        }

        $parameters = array(
            "proxyNameSpace" => $this->getProxyNameSpace($originalClassName),
            "proxyClassName" => $this->getProxyClassName($originalClassName),
            "shortClassName" => $this->getShortClassName($originalClassName),
            "originalClassName" => $originalClassName
        );
        $templateFile = __DIR__ . '/ProxyTemplate.php.tpl';

        return $this->generateProxyClass($originalClassName, file_get_contents($templateFile), $parameters);
    }

    public function getProxyNameSpace($originalClassName)
    {
        $proxyNameSpaceTab = explode('\\', trim($this->getProxyClassName($originalClassName), '\\'));
        array_pop($proxyNameSpaceTab);

        return implode('\\', $proxyNameSpaceTab);
    }

    public function getProxyClassName($originalClassName)
    {
        return '\\' . __NAMESPACE__ . $originalClassName;
    }

    public function getShortClassName($originalClassName)
    {
        $proxyNameSpaceTab = explode('\\', $originalClassName);

        return array_pop($proxyNameSpaceTab);
    }

    public function generateProxyClass($originalClassName, $proxyTemplateContent, $parameters = array())
    {
        $proxyClassName = $this->getProxyClassName($originalClassName);
        if (class_exists($proxyClassName)) {
            return $proxyClassName;
        }
        $proxyClassDefinition = $this->replaceInTemplate($proxyTemplateContent, $parameters);
        eval($proxyClassDefinition);

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
