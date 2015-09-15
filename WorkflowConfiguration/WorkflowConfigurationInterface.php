<?php

namespace Kitpages\WorkflowBundle\WorkflowConfiguration;

interface WorkflowConfigurationInterface
{
    /**
     * Name of the Workflow.
     *
     * @return string
     */
    public function getName();

    /**
     * Initial state of the Workflow.
     *
     * @return StateConfigurationInterface
     */
    public function getInitialState();

    /**
     * Array of all the states composing the workflow.
     *
     * @return array<StateInterface>
     */
    public function getStateList();

    /**
     * @param $key
     *
     * @return StateConfigurationInterface
     *
     * @throws \Exception
     */
    public function getState($key);

    /**
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function getParameter($key, $default = null);

    /**
     * @return array key=>value
     */
    public function getParameterList();
}
