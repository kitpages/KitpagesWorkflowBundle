<?php


namespace Kitpages\WorkflowBundle\WorkflowConfiguration;


interface EventConfigurationInterface
{

    /**
     * The next possible states with the step result as key
     * @return array
     */
    public function getNextStateList();

    /**
     * The identifier for the step
     * @return string
     */
    public function getStepKey();

    /**
     * Next state key if there is no step to execute
     * @return mixed
     */
    public function getAutoNextStateKey();

    /**
     * Next state key if a step has been executed
     * @param $stepResult
     * @return mixed
     */
    public function getNextStateKey($stepResult);

    /**
     * The parameterList for the step : key=>value
     * @return array
     */
    public function getStepParameterList();


    /**
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function getStepParameter($key, $default = null);

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setStepParameter($key, $value);

} 