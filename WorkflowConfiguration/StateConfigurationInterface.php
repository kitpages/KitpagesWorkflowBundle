<?php


namespace Kitpages\WorkflowBundle\WorkflowConfiguration;


interface StateConfigurationInterface
{

    /**
     * Name of this state
     * @return string
     */
    public function getName();

    /**
     * Accepted Eventlist
     * @return array
     */
    public function getEventList();

    /**
     * Get an event by its key
     * @param string $event
     * @return EventConfigurationInterface|null
     */
    public function getEvent($event);

    /**
     * Sub workflow parametersList : key=> value
     * @return array
     */
    public function getSubWorkflowParameterList();

    /**
     * Sub workflow name
     * @return string
     */
    public function getSubWorkflowName();

    /**
     * @return \Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface
     */
    public function getSubWorkflowConfiguration();

    /**
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function getSubWorkflowParameter($key, $default = null);

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setSubWorkflowParameter($key, $value);

} 