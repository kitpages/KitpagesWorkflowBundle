<?php

namespace Kitpages\WorkflowBundle\WorkflowConfiguration;

class StateConfiguration implements StateConfigurationInterface
{

    /**
     * @var string
     */
    protected $name;
    /**
     * @var array<EventInterface>
     */
    protected $eventList = array();

    /**
     * @var array<string>
     */
    protected $nextStateList = array();
    /**
     * @var string
     */
    protected $subWorkflowName;

    /**
     * @var WorkflowConfigurationInterface
     */
    protected $subWorkflowConfiguration;

    /**
     * @var array
     */
    protected $subWorkflowParameterList = array();

    /**
     * @param array $eventList
     */
    public function setEventList($eventList)
    {
        $this->eventList = $eventList;
    }

    /**
     * @param $eventKey
     * @param EventConfigurationInterface $event
     * @return $this
     */
    public function setEvent($eventKey, EventConfigurationInterface $event)
    {
        $this->eventList[$eventKey] = $event;

        return $this;
    }

    /**
     * Get an event by its key
     * @param string $event
     * @return EventConfigurationInterface|null
     */
    public function getEvent($event)
    {
        if (array_key_exists($event, $this->eventList)) {
            return $this->eventList[$event];
        }

        return null;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $nextStateList
     */
    public function setNextStateList($nextStateList)
    {
        $this->nextStateList = $nextStateList;
    }

    /**
     * Name of this state
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Accepted EventList
     * @return array
     */
    public function getEventList()
    {
        return $this->eventList;
    }


    /**
     * The next possible states with the step result as key
     * @return array
     */
    public function getNextStateList()
    {
        return $this->nextStateList;
    }

    /**
     * @param string $subWorkflowName
     */
    public function setSubWorkflowName($subWorkflowName)
    {
        $this->subWorkflowName = $subWorkflowName;
    }

    /**
     * @return string
     */
    public function getSubWorkflowName()
    {
        return $this->subWorkflowName;
    }

    /**
     * @param array $subWorkflowParametersList
     */
    public function setSubWorkflowParametersList($subWorkflowParametersList)
    {
        $this->subWorkflowParameterList = $subWorkflowParametersList;
    }

    /**
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function getSubWorkflowParameter($key, $default = null)
    {
        if (array_key_exists($key, $this->subWorkflowParameterList)) {
            return $this->subWorkflowParameterList[$key];
        }

        return $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setSubWorkflowParameter($key, $value)
    {
        $this->subWorkflowParameterList[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getSubWorkflowParameterList()
    {
        return $this->subWorkflowParameterList;
    }

    /**
     * @param \Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface $subWorkflowConfiguration
     */
    public function setSubWorkflowConfiguration($subWorkflowConfiguration)
    {
        $this->subWorkflowConfiguration = $subWorkflowConfiguration;
    }

    /**
     * @return \Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface
     */
    public function getSubWorkflowConfiguration()
    {
        return $this->subWorkflowConfiguration;
    }


}