<?php
namespace Kitpages\WorkflowBundle\Manager;

use Kitpages\StepBundle\Step\StepEvent;
use Kitpages\StepBundle\Step\StepManager;
use Kitpages\WorkflowBundle\Event\ActionEvent;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;
use Kitpages\WorkflowBundle\Proxy\ProxyGenerator;
use Kitpages\WorkflowBundle\Step\AbstractWorkflowStep;
use Kitpages\WorkflowBundle\Storage\WorkflowStorageStrategyInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\EventConfigurationInterface;
use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkflowManager
    implements WorkflowManagerInterface
{
    /**
     * @var null|\Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher = null;
    /**
     * @var WorkflowStorageStrategyInterface
     */
    protected $storageStrategy;

    /**
     * @var \Kitpages\StepBundle\Step\StepManager
     */
    protected $stepManager;
    /**
     * @var WorkflowInterface[]
     */
    protected $workflowList = array();

    /**
     * @var array<WorkflowConfigurationInterface>
     */
    protected $workflowConfigurationList = array();

    /**
     * @var string
     */
    protected $defaultStepName;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param WorkflowStorageStrategyInterface $storageStrategy
     * @param StepManager $stepManager
     * @param $defaultStepName
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        WorkflowStorageStrategyInterface $storageStrategy,
        StepManager $stepManager,
        $defaultStepName,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->storageStrategy = $storageStrategy;
        $this->stepManager = $stepManager;
        $this->defaultStepName = $defaultStepName;
        $this->logger = $logger;
    }

    /**
     * @param string $key
     * @param WorkflowConfigurationInterface $workflowConfiguration
     * @param array $parameterList
     * @param WorkflowInterface $parentWorkflow
     * @return WorkflowInterface
     */
    public function createWorkflow($key, WorkflowConfigurationInterface $workflowConfiguration, array $parameterList = [], WorkflowInterface $parentWorkflow = null)
    {
        // generate step
        $workflow = $this->generateWorkflowProxy();
        // init workflow instance
        $workflow->setKey($key);
        $workflow->setWorkflowConfiguration($workflowConfiguration);

        $workflow->setParentWorkflow($parentWorkflow);
        if ($parentWorkflow instanceof WorkflowInterface) {
            $parentWorkflow->setSubWorkflow($workflow);
        }

        foreach ($workflowConfiguration->getParameterList() as $paramKey => $value) {
            $workflow->setParameter($paramKey, $value);
        }


        foreach ($parameterList as $paramKey => $value) {
            $workflow->setParameter($paramKey, $value);
        }

        $this->addWorkflow($key, $workflow);
        $workflow->setCurrentState($workflowConfiguration->getInitialState()->getName());

        return $workflow;
    }

    /**
     * Creates subWorkflows if needed
     * @param WorkflowInterface $workflow
     * @return $this
     */
    public function initializeWorkflow(WorkflowInterface $workflow)
    {

        $stateConfig = $workflow->getWorkflowConfiguration()->getState($workflow->getCurrentState());
        $subWorkflowName = $stateConfig->getSubWorkflowName();

        $createSubWorkflow = false;

        if ($subWorkflowName) {
            if ($workflow->getSubWorkflow() instanceof WorkflowInterface) {
                if ($workflow->getPreviousState() == $workflow->getCurrentState()) {
                    //The current subworkflow is the same, it should not be deleted or reinitialised
                } else {
                    //The current subworkflow is from a previous different state, it must be deleted, and a correct one instanciated.
                    if ($workflow->getSubWorkflow() instanceof WorkflowInterface) {
                        $this->deleteWorkflow($workflow->getSubWorkflow()->getKey());
                    }
                    $createSubWorkflow = true;
                }
            } else {
                $createSubWorkflow = true;
            }

            if ($createSubWorkflow) {
                $subWorkflowConfiguration = $this->getWorkflowConfiguration($subWorkflowName);
                $subWorkflowParameters = $stateConfig->getSubWorkflowParameterList();

                $resolvedParameters = [];

                foreach ($subWorkflowParameters as $paramKey => $value) {
                    $resolvedParameters[$paramKey] = $workflow->resolveParameter($value);
                }

                $subworkflow = $this->createWorkflow(
                    $workflow->getKey() . '.' . $workflow->getVerboseState() . '.' . $subWorkflowName,
                    $subWorkflowConfiguration,
                    $resolvedParameters,
                    $workflow
                );

                $this->initializeWorkflow($subworkflow);
            }
        } else {
            if ($workflow->getSubWorkflow() instanceof WorkflowInterface) {
                $this->deleteWorkflow($workflow->getSubWorkflow()->getKey());
            }
        }

        return $this;
    }

    /**
     * @return WorkflowInterface
     */
    public function createEmptyWorkflow()
    {
        return $this->generateWorkflowProxy();
    }


    /**
     * Delete a workflow and all it's subworkflows and update parentWorklow if needed
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function deleteWorkflow($key)
    {
        if (!array_key_exists($key, $this->workflowList)) {
            throw new \Exception('Workflow "' . $key . '" cannot be deleted because it is not managed');
        }
        /** @var $workflow WorkflowInterface */
        $workflow = $this->workflowList[$key];
        $subWorkflow = $workflow->getSubWorkflow();
        if ($subWorkflow instanceof WorkflowInterface) {
            $this->deleteWorkflow($subWorkflow->getKey());
        }
        $parentWorkflow = $workflow->getParentWorkflow();
        if ($parentWorkflow instanceof WorkflowInterface) {
            $parentWorkflow->setSubWorkflow(null);
        }
        unset($this->workflowList[$key]);
        unset($workflow);
    }

    /**
     * Generate an empty workflow proxy instance
     * @return WorkflowInterface
     */
    public function generateWorkflowProxy()
    {
        $className = '\\Kitpages\\WorkflowBundle\\Model\\Workflow';
        $proxyGenerator = new ProxyGenerator();
        $workflow = $proxyGenerator->generateProcessProxy($className);
        $workflow->__workflowProxySetEventDispatcher($this->eventDispatcher);

        return $workflow;
    }

    /**
     * @return array of WorkflowInterface
     */
    public function getWorkflowList()
    {
        return $this->workflowList;
    }

    /**
     * @param $key
     * @return WorkflowInterface
     * @throws \Exception
     */
    public function getWorkflow($key)
    {
        if (array_key_exists($key, $this->workflowList)) {
            return $this->workflowList[$key];
        }
        throw new \Exception('Workflow "' . $key . '" is not managed');
    }

    /**
     * @param $key
     * @param WorkflowInterface $workflow
     * @return $this
     */
    public function addWorkflow($key, WorkflowInterface $workflow)
    {
        $this->workflowList[$key] = $workflow;

        return $this;
    }

    /**
     *
     * @param $key
     * @return bool remove successful
     */
    public function removeWorkflow($key)
    {
        if (array_key_exists($key, $this->workflowList)) {
            unset($this->workflowList[$key]);

            return true;
        }

        return false;
    }

    /**
     * @return array of WorkflowConfigurationInterface
     */
    public function getWorkflowConfigurationList()
    {
        return $this->workflowConfigurationList;
    }

    /**
     * @param $key
     * @return WorkflowConfigurationInterface
     * @throws \Exception
     */
    public function getWorkflowConfiguration($key)
    {
        if (array_key_exists($key, $this->workflowConfigurationList)) {
            return $this->workflowConfigurationList[$key];
        }
        throw new \Exception('WorkflowConfiguration "' . $key . '" is not managed');
    }

    /**
     * @param $key
     * @param WorkflowConfigurationInterface $workflowConfiguration
     * @return $this
     * @throws \Exception
     */
    public function addWorkflowConfiguration($key, WorkflowConfigurationInterface $workflowConfiguration)
    {
        if (array_key_exists($key, $this->workflowConfigurationList)) {
            throw new \Exception('Workflow configuration key "' . $key . '" is already used');
        }
        $this->workflowConfigurationList[$key] = $workflowConfiguration;

        return $this;
    }

    public function onActionEvent(ActionEvent $event)
    {
        $catcherList = $this->workflowList;
        while (count($catcherList) > 0) {
            /** @var $workflow WorkflowInterface */
            $workflow = end($catcherList);
            $finalWorkflow = $workflow->getFinalSubWorkflow();
            $currentWorkflow = $finalWorkflow->getParentWorkflow();
            $this->applyEvent($event, $finalWorkflow);
            unset($catcherList[$finalWorkflow->getKey()]);
            while ($currentWorkflow instanceof WorkflowInterface) {
                $this->applyEvent($event, $currentWorkflow);
                unset($catcherList[$currentWorkflow->getKey()]);
                $currentWorkflow = $currentWorkflow->getParentWorkflow();
            }
        }

        return $this;
    }

    /**
     * @param ActionEvent $event
     * @param WorkflowInterface $workflow
     */
    protected function applyEvent(ActionEvent $event, WorkflowInterface $workflow)
    {

        $eventConfiguration = $workflow->getEventConfiguration($event->getKey());

        if ($eventConfiguration instanceof EventConfigurationInterface) {

            $catcherList[] = $workflow->getKey();

            $resolvedParameters = [];
            foreach ($eventConfiguration->getStepParameterList() as $key => $value) {
                $resolvedParameters[$key] = $workflow->resolveParameter($value);
            }
            $resolvedParameters['_event'] = $event;
            $resolvedParameters['_workflow'] = $workflow;
            $configurationOverride = [
                'parameter_list' => $resolvedParameters
            ];

            $stepName = $eventConfiguration->getAutoNextStateKey() ? $this->defaultStepName : $eventConfiguration->getStepKey();
            $this->logger->info(sprintf('Applying event "%s" to workflow %s, executing step %s', $event->getKey(), $workflow->getKey(), $stepName));

            $initState = $workflow->getVerboseState();
            /**
             * @var AbstractWorkflowStep
             */
            $step = $this->stepManager->getStep($stepName, $configurationOverride);
            $result = $step->execute();
            $nextState = $eventConfiguration->getNextStateKey($result);
            //Check if step did not changed the workflow state:
            if ($initState == $workflow->getVerboseState() && array_key_exists($workflow->getKey(), $this->workflowList)) {
                $workflow->setCurrentState($nextState);
                $this->initializeWorkflow($workflow);
            } else {
                $this->logger->info(sprintf('Aborting changing state to "%s" because step execution changed workflow state', $nextState));
            }
        }
    }

    /**
     *
     * @param $key
     * @return bool remove successful
     */
    public function removeWorkflowConfiguration($key)
    {
        if (array_key_exists($key, $this->workflowConfigurationList)) {
            unset($this->workflowConfigurationList[$key]);

            return true;
        }

        return false;
    }

    /**
     * A storable workflow list
     * @return mixed
     */
    public function getStorableWorkflowList()
    {
        return $this->storageStrategy->createStorableWorkflowList($this->workflowList);
    }

    /**
     * @param mixed $storableWorkflowList
     * @return WorkflowManager
     */
    public function createWorkflowListFromStorable($storableWorkflowList)
    {
        $this->workflowList = $this->storageStrategy->createFromStoredWorkflowList($this, $storableWorkflowList);

        return $this;
    }

    public function onStepExecute(StepEvent $event)
    {
        //TODO: do something here if needed
    }
}