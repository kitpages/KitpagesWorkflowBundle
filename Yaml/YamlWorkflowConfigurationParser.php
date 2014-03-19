<?php


namespace Kitpages\WorkflowBundle\Yaml;


use Kitpages\WorkflowBundle\WorkflowConfiguration\EventConfiguration;
use Kitpages\WorkflowBundle\WorkflowConfiguration\EventConfigurationInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\StateConfiguration;
use Kitpages\WorkflowBundle\WorkflowConfiguration\StateConfigurationInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfiguration;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationParserInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlWorkflowConfigurationParser implements WorkflowConfigurationParserInterface
{

    /**
     * @param mixed $yaml
     * @return WorkflowConfigurationInterface
     * @throws \Exception
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public static function parse($yaml)
    {
        $workflowConfig = new WorkflowConfiguration();
        try {
            $configArray = Yaml::parse($yaml);
        } catch (ParseException $e) {
            //TODO : Do something smarter
            throw $e;
        }

        try {
            self::handleWorkflow($workflowConfig, $configArray);
        } catch (\Exception $e) {
            throw $e;
        }

        return $workflowConfig;
    }

    protected static function handleWorkflow(WorkflowConfiguration $workflowConfig, array $configArray)
    {
        if (!array_key_exists('workflow_definition', $configArray)) {
            throw new \Exception('This is not a worflow definition (must start with "workflow_definition")');
        }
        $configArray = $configArray['workflow_definition'];

        if (strpos($configArray['name'], '.') !== false) {
            throw new \Exception(sprintf('A workflow definition "%s" has a forbidden dot (.) in the "name" parameter', $configArray['name']));
        }
        $workflowConfig->setName($configArray['name']);

        if (array_key_exists('parameter_list', $configArray)) {
            $workflowConfig->setParameterList($configArray['parameter_list'] ? $configArray['parameter_list'] : []);
        }

        foreach ($configArray['state_list'] as $name => $stateArray) {
            if (strpos($name, '.') !== false) {
                throw new \Exception(sprintf('A workflow definition "%s" has a forbidden dot (.) in the name of the state "%s"', $configArray['name'], $name));
            }
            self::handleState($workflowConfig, $name, $stateArray);
        }
        $workflowConfig->setInitialState($workflowConfig->getState($configArray['init_state']));

    }

    /**
     * @param WorkflowConfiguration $workflowConfig
     * @param $name
     * @param $stateArray
     * @throws \Exception
     */
    protected static function handleState(WorkflowConfiguration $workflowConfig, $name, $stateArray)
    {
        $state = new StateConfiguration();
        $state->setName($name);
        $workflowConfig->addState($name, $state);

        if (array_key_exists('workflow', $stateArray)) {
            $state->setSubWorkflowName($stateArray['workflow']['name']);
            if (array_key_exists('parameter_list', $stateArray['workflow'])) {
                $parameters = $stateArray['workflow']['parameter_list'];
                if (is_array($parameters)) {
                    foreach ($stateArray['workflow']['parameter_list'] as $key => $value) {
                        $state->setSubWorkflowParameter($key, $value);
                    }
                }
            }
        }

        if (array_key_exists('event_list', $stateArray) && is_array($stateArray['event_list'])) {
            foreach ($stateArray['event_list'] as $eventName => $eventArray) {
                if (!is_array($eventArray)) {
                    throw new \Exception(sprintf('Cannot parse "%s" because an event configuration in event_list "%s" must not be empty', $name, $eventName));
                }
                $event = new EventConfiguration();
                $state->setEvent($eventName, $event);
                if (!array_key_exists('next_state', $eventArray)) {
                    throw new \Exception(sprintf('Cannot parse "%s" because an event configuration in event_list "%s" must have a "next_state" configuration', $name, $eventName));
                }

                if (is_array($eventArray['next_state'])) {
                    foreach ($eventArray['next_state'] as $stepResult => $stateKey) {
                        $event->setNextStateKey($stepResult, $stateKey);
                    }
                    if (!array_key_exists('step', $eventArray) || !$eventArray['step']) {
                        throw new \Exception(sprintf('Cannot parse "%s" because an event configuration that has multiple next states must have a "step" configuration', $name));
                    }
                    $event->setStepKey($eventArray['step']['name']);
                    if (array_key_exists('parameter_list', $eventArray['step'])) {
                        foreach ($eventArray['step']['parameter_list'] as $key => $value) {
                            $event->setStepParameter($key, $value);
                        }
                    }

                } else {
                    $event->setAutoNextStateKey($eventArray['next_state']);
                }

            }
        }
    }
} 