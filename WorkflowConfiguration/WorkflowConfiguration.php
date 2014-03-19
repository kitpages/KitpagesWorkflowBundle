<?php


namespace Kitpages\WorkflowBundle\WorkflowConfiguration;


class WorkflowConfiguration implements WorkflowConfigurationInterface
{

    protected $name;
    protected $initialState;
    protected $stateList = array();
    protected $parameterList = array();

    /**
     * @param StateConfigurationInterface $initialState
     */
    public function setInitialState($initialState)
    {
        $this->initialState = $initialState;
    }

    /**
     * @return StateConfiguration
     */
    public function getInitialState()
    {
        return $this->initialState;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $parameterList
     */
    public function setParameterList(array $parameterList)
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
     * @param array $stateList
     */
    public function setStateList($stateList)
    {
        $this->stateList = $stateList;
    }

    /**
     * @return array
     */
    public function getStateList()
    {
        return $this->stateList;
    }

    /**
     * @param $key
     * @return StateConfigurationInterface
     * @throws \Exception
     */
    public function getState($key)
    {
        if (!array_key_exists($key, $this->stateList)) {
            throw new \Exception('State "' . $key . '" is not in workflow configuration state list');
        }

        return $this->stateList[$key];
    }

    /**
     * @param $key
     * @param StateConfiguration $state
     * @return $this
     * @throws \Exception
     */
    public function addState($key, StateConfiguration $state)
    {
        if (array_key_exists($key, $this->stateList)) {
            throw new \Exception('State "' . $key . '" has already been defined');
        }
        $this->stateList[$key] = $state;

        return $this;
    }

    /**
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        if (array_key_exists($key, $this->parameterList)) {
            return $this->parameterList[$key];
        }

        return $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setParameter($key, $value)
    {
        $this->parameterList[$key] = $value;

        return $this;
    }
}