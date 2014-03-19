<?php


namespace Kitpages\WorkflowBundle\Storage;

use Kitpages\WorkflowBundle\Manager\WorkflowManagerInterface;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;

class JsonWorkflowStorageStrategy implements WorkflowStorageStrategyInterface
{

    protected $simpleAttributeList = [
        'key',
        'currentState',
        'previousState',
        'data',
        'parameterList'
    ];

    protected $objectAttributeList = [
        'workflowConfiguration'
    ];

    /**
     * Returns a workflow in a storable format
     * @param WorkflowInterface $workflow
     * @return string
     */
    public function createStorableWorkflow(WorkflowInterface $workflow)
    {
        return json_encode($this->normalizeWorkflow($workflow));
    }

    protected function normalizeWorkflow(WorkflowInterface $workflow)
    {
        $array = [];

        foreach ($this->simpleAttributeList as $attribute) {
            $method = 'get' . ucfirst($attribute);
            $array[$attribute] = $workflow->$method();
        }
        foreach ($this->objectAttributeList as $attribute) {
            $method = 'get' . ucfirst($attribute);
            $array[$attribute] = serialize($workflow->$method());
        }

        $subWorkflow = $workflow->getSubWorkflow();
        if ($subWorkflow instanceof WorkflowInterface) {
            $array['subWorkflowKey'] = $subWorkflow->getKey();
        } else {
            $array['subWorkflowKey'] = null;
        }

        $parentWorkflow = $workflow->getParentWorkflow();
        if ($parentWorkflow instanceof WorkflowInterface) {
            $array['parentWorkflowKey'] = $parentWorkflow->getKey();
        } else {
            $array['parentWorkflowKey'] = null;
        }

        return $array;
    }

    /**
     * Returns the list under a storable format
     * @param array <WorkflowInterface> $workflowList
     * @return string
     */
    public function createStorableWorkflowList(array $workflowList)
    {
        $array = [];
        /** @var $workflow WorkflowInterface */
        foreach ($workflowList as $workflow) {
            $array[$workflow->getKey()] = $this->normalizeWorkflow($workflow);
        }

        return json_encode($array);
    }

    /**
     * Creates a Workflow from a stored workflow
     * @param WorkflowManagerInterface $wfm
     * @param mixed $storedWorkflow
     * @return WorkflowInterface
     * @throws \Exception
     */
    public function createFromStoredWorkflow(WorkflowManagerInterface $wfm, $storedWorkflow)
    {
        $deserializedArray = json_decode($storedWorkflow, true);

        if (null === $deserializedArray || !is_array($deserializedArray)) {
            throw new \Exception('Could not json_decode stored Workflow');
        }

        $workflow = $wfm->createEmptyWorkflow();

        return $this->denormalize($deserializedArray, $workflow);
    }

    /**
     * @param array $array
     * @param WorkflowInterface $workflow
     * @return WorkflowInterface
     */
    protected function denormalize(array $array, WorkflowInterface $workflow)
    {

        foreach ($this->simpleAttributeList as $attribute) {
            $method = 'set' . ucfirst($attribute);
            //true in the second parameter is for the WorkflowProxy implementation to skip dispatching events for the workflow creation.
            $workflow->$method($array[$attribute], true);
        }
        foreach ($this->objectAttributeList as $attribute) {
            $method = 'set' . ucfirst($attribute);
            $workflow->$method(unserialize($array[$attribute]));
        }

        $workflow->setParentWorkflow($array['parentWorkflowKey']);
        $workflow->setSubWorkflow($array['subWorkflowKey']);

        return $workflow;
    }

    /**
     * Creates a Workflow array from a stored workflow list
     * @param WorkflowManagerInterface $wfm
     * @param mixed $storedWorkflowList
     * @return array<WorkflowInterface>
     * @throws \Exception
     */
    public function createFromStoredWorkflowList(WorkflowManagerInterface $wfm, $storedWorkflowList)
    {
        $normalized = json_decode($storedWorkflowList, true);
        if (!is_array($normalized)) {
            throw new \Exception('Could not json_decode stored WorkflowList');
        }

        $workflowList = [];

        foreach ($normalized as $key => $serializedWorkflow) {
            $workflow = $wfm->createEmptyWorkflow();
            $workflowList[$key] = $this->denormalize($serializedWorkflow, $workflow);
        }

        //Create parent and sub workflow references
        /** @var $workflow WorkflowInterface */
        foreach ($workflowList as $key => $workflow) {
            $parentKey = $workflow->getParentWorkflow();
            if (is_string($parentKey)) {
                $workflow->setParentWorkflow($workflowList[$parentKey]);
            }

            $subKey = $workflow->getSubWorkflow();
            if (is_string($subKey)) {
                $workflow->setSubWorkflow($workflowList[$subKey]);
            }
        }

        return $workflowList;
    }
}