<?php
namespace Kitpages\WorkflowBundle\Model;

use Kitpages\WorkflowBundle\WorkflowConfiguration\EventConfigurationInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface;

interface WorkflowInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @param string $key
     * @return WorkflowInterface
     */
    public function setKey($key);

    /**
     * @return WorkflowConfigurationInterface
     */
    public function getWorkflowConfiguration();

    /**
     * @param WorkflowConfigurationInterface $workflowConfiguration
     * @return $this
     */
    public function setWorkflowConfiguration(WorkflowConfigurationInterface $workflowConfiguration);

    /**
     * Returns an array of the event configuration that may change the current state of the workflow
     * @return array<EventConfiguration>
     */
    public function getAcceptableEventConfigurationList();

    /**
     * @return string
     */
    public function getCurrentState();


    /**
     * Returns the current state and subworkflow current states delemited by a dot "."
     * @return string
     */
    public function getVerboseState();

    /**
     * @param string $state
     * @return $this
     */
    public function setCurrentState($state);

    /**
     * @return WorkflowInterface|null
     */
    public function getSubWorkflow();

    /**
     * @return WorkflowInterface|null
     */
    public function getParentWorkflow();

    /**
     * @param WorkflowInterface|string $parentWorkflow
     * @return $this
     */
    public function setParentWorkflow($parentWorkflow);

    /**
     * @return WorkflowInterface
     */
    public function getFinalParentWorkflow();

    /**
     * @param WorkflowInterface|string $subWorkflow
     * @return $this
     */
    public function setSubWorkflow($subWorkflow);

    /**
     * @return WorkflowInterface
     */
    public function getFinalSubWorkflow();

    /**
     * @return string
     */
    public function getPreviousState();

    /**
     * return the value defined by $key
     * @param $key
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function get($key);

    /**
     * If an event can be managed in the current state, returns the event configuration, null otherwise
     * @param $actionEventName
     * @return null|EventConfigurationInterface
     */
    public function getEventConfiguration($actionEventName);

    /**
     * set
     * @param $key
     * @param $val
     * @return $this
     */
    public function set($key, $val);

    /**
     * return the value defined by $key
     * @param $key
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function getParameter($key);

    /**
     * returns all of the parameters
     * @return array
     */
    public function getParameterList();
    /**
     * set
     * @param $key
     * @param $val
     * @return $this
     */
    public function setParameter($key, $val);

    /**
     * Returns the actual value of a parameter if it is an alias (ex: %param1% => value1)
     *
     * @param $parameterValue
     * @return mixed
     * @throws \Exception
     */
    public function resolveParameter($parameterValue);
}