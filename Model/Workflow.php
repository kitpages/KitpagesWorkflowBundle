<?php

namespace Kitpages\WorkflowBundle\Model;

use Kitpages\WorkflowBundle\WorkflowConfiguration\EventConfiguration;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface;

class Workflow implements WorkflowInterface
{
    const WORKFLOW_PARAMETER_WRAPPER = '%';

    /**
     * @var WorkflowInterface
     */
    protected $subWorkflow;
    /**
     * @var WorkflowInterface
     */
    protected $parentWorkflow;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $currentState;
    /**
     * @var string
     */
    protected $previousState;
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $parameterList = array();

    /**
     * @var WorkflowConfigurationInterface
     */
    protected $workflowConfiguration;

    public function __contstruct()
    {
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return WorkflowInterface
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return WorkflowConfigurationInterface
     */
    public function getWorkflowConfiguration()
    {
        return $this->workflowConfiguration;
    }

    public function setWorkflowConfiguration(WorkflowConfigurationInterface $workflowConfiguration)
    {
        $this->workflowConfiguration = $workflowConfiguration;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setCurrentState($state)
    {
        $this->previousState = $this->currentState;
        $this->currentState = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * @param string $previousState
     */
    public function setPreviousState($previousState)
    {
        $this->previousState = $previousState;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * set.
     *
     * @param $key
     * @param $val
     *
     * @return $this
     */
    public function set($key, $val)
    {
        $this->data[$key] = $val;

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $parameterList
     */
    public function setParameterList($parameterList)
    {
        $this->parameterList = $parameterList;
    }

    /**
     * @return array
     */
    public function getParameterList()
    {
        return $this->parameterList;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed|void
     */
    public function getParameter($key, $default = null)
    {
        if (array_key_exists($key, $this->parameterList)) {
            return $this->parameterList[$key];
        }

        return $default;
    }

    /**
     * set.
     *
     * @param $key
     * @param $val
     *
     * @return $this
     */
    public function setParameter($key, $val)
    {
        $this->parameterList[$key] = $val;

//        $this->workflowConfiguration->injectParameter($key, $val);

        return $this;
    }

    /**
     * @return WorkflowInterface|string|null
     */
    public function getSubWorkflow()
    {
        return $this->subWorkflow;
    }

    /**
     * @return WorkflowInterface|string|null
     */
    public function getParentWorkflow()
    {
        return $this->parentWorkflow;
    }

    /**
     * @param WorkflowInterface|string $subWorkflow
     *
     * @return $this
     */
    public function setSubWorkflow($subWorkflow)
    {
        $this->subWorkflow = $subWorkflow;

        return $this;
    }

    /**
     * @param WorkflowInterface|string $parentWorkflow
     *
     * @return $this
     */
    public function setParentWorkflow($parentWorkflow)
    {
        $this->parentWorkflow = $parentWorkflow;

        return $this;
    }

    public function getVerboseState()
    {
        $delimiter = '.';
        $completeState = $this->currentState;
        if ($this->subWorkflow instanceof WorkflowInterface) {
            $completeState .= $delimiter.$this->subWorkflow->getVerboseState();
        }

        return $completeState;
    }

    /**
     * @return WorkflowInterface
     */
    public function getFinalSubWorkflow()
    {
        if ($this->subWorkflow instanceof WorkflowInterface) {
            return $this->subWorkflow->getFinalSubWorkflow();
        }

        return $this;
    }

    /**
     * @return WorkflowInterface
     */
    public function getFinalParentWorkflow()
    {
        if ($this->parentWorkflow instanceof WorkflowInterface) {
            return $this->parentWorkflow->getFinalParentWorkflow();
        }

        return $this;
    }

    /**
     * Returns the name of the parameter if it is not resolved, false otherwise.
     *
     * @param $parameterValue
     *
     * @return bool|string
     */
    protected function isNotResolved($parameterValue)
    {
        if (!is_string($parameterValue)) {
            return false;
        }
        $length = strlen($parameterValue);
        if ($length < 3) {
            return false;
        }
        if (
            $parameterValue[0] == self::WORKFLOW_PARAMETER_WRAPPER
            && $parameterValue[strlen($parameterValue) - 1] == self::WORKFLOW_PARAMETER_WRAPPER
        ) {
            return substr($parameterValue, 1, -1);
        }

        return false;
    }

    /**
     * @param $actionEventName
     *
     * @return null|EventConfiguration
     */
    public function getEventConfiguration($actionEventName)
    {
        $stateConfig = $this->workflowConfiguration->getState($this->currentState);
        $eventConfig = $stateConfig->getEvent($actionEventName);

        return $eventConfig;
    }

    /**
     * Returns an array of the event configuration that may change the current state of the workflow.
     *
     * @return array<EventConfiguration>
     */
    public function getAcceptableEventConfigurationList()
    {
        $stateConfig = $this->getWorkflowConfiguration()->getState($this->getCurrentState());

        return $stateConfig->getEventList();
    }

    /**
     * @param $parameterValue
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function resolveParameter($parameterValue)
    {
        $notResolvedKey = self::isNotResolved($parameterValue);
        if ($notResolvedKey) {
            $resolvedValue = $this->getParameter($notResolvedKey);
            if (null === $resolvedValue) {
                throw new \Exception(sprintf('The parameter "%s" is not defined in WorkflowConfiguration "%s"', $notResolvedKey, $this->getKey()));
            }

            return $resolvedValue;
        }

        return $parameterValue;
    }
}
