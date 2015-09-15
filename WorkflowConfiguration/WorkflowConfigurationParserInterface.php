<?php

namespace Kitpages\WorkflowBundle\WorkflowConfiguration;

interface WorkflowConfigurationParserInterface
{
    /**
     * @param mixed $source
     *
     * @return WorkflowConfigurationInterface
     */
    public static function parse($source);
}
