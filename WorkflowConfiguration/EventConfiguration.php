<?php


namespace Kitpages\WorkflowBundle\WorkflowConfiguration;


class EventConfiguration implements EventConfigurationInterface
{
    /**
     * @var string
     */
    protected $stepKey;

    /**
     * @var array
     */
    protected $nextStateList = array();

    /**
     * @var string
     */
    protected $autoNextStateKey;

    /**
     * @var array
     */
    protected $stepParameterList = array();

    /**
     * @param array $stepParameterList
     */
    public function setStepParameterList($stepParameterList)
    {
        $this->stepParameterList = $stepParameterList;
    }

    /**
     * @return array
     */
    public function getStepParameterList()
    {
        return $this->stepParameterList;
    }

    /**
     * @param mixed $autoNextStateKey
     */
    public function setAutoNextStateKey($autoNextStateKey)
    {
        $this->autoNextStateKey = $autoNextStateKey;
    }

    /**
     * @return mixed
     */
    public function getAutoNextStateKey()
    {
        return $this->autoNextStateKey;
    }

    /**
     * @param array $nextStateList
     */
    public function setNextStateList($nextStateList)
    {
        $this->nextStateList = $nextStateList;
    }

    /**
     * @return array
     */
    public function getNextStateList()
    {
        return $this->nextStateList;
    }

    /**
     * @param string $stepKey
     */
    public function setStepKey($stepKey)
    {
        $this->stepKey = $stepKey;
    }

    /**
     * @return string
     */
    public function getStepKey()
    {
        return $this->stepKey;
    }

    /**
     * @param $stepResult
     * @return string
     * @throws \Exception
     */
    public function getNextStateKey($stepResult)
    {
        if($this->autoNextStateKey){
            return $this->autoNextStateKey;
        }

        if (array_key_exists($stepResult, $this->nextStateList)) {
            return $this->nextStateList[$stepResult];
        }

        throw new \Exception(sprintf('Tried to get a next state for step result "%s" but none is configured for this event.', $stepResult));
    }

    /**
     * @param $key
     * @param $nextStateKey
     * @return $this|mixed
     */
    public function setNextStateKey($key, $nextStateKey)
    {
        $this->nextStateList[$key] = $nextStateKey;

        return $this;
    }

    /**
     * @param mixed $key
     * @param null $default
     * @return mixed|null
     */
    public function getStepParameter($key, $default = null)
    {
        if (array_key_exists($key, $this->stepParameterList)) {
            return $this->stepParameterList[$key];
        }

        return $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setStepParameter($key, $value)
    {
        $this->stepParameterList[$key] = $value;

        return $this;
    }


}