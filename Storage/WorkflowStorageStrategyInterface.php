<?php

namespace Kitpages\WorkflowBundle\Storage;

use Kitpages\WorkflowBundle\Manager\WorkflowManagerInterface;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;

interface WorkflowStorageStrategyInterface
{
    /**
     * Returns a workflow in a storable format.
     *
     * @param WorkflowInterface $workflow
     *
     * @return mixed
     */
    public function createStorableWorkflow(WorkflowInterface $workflow);

    /**
     * Returns the list under a storable format.
     *
     * @param array <WorkflowInterface> $workflowList
     *
     * @return mixed
     */
    public function createStorableWorkflowList(array $workflowList);

    /**
     * Creates a Workflow from a stored workflow.
     *
     * @param WorkflowManagerInterface $wfm
     * @param mixed                    $storedWorkflow
     *
     * @return WorkflowInterface
     */
    public function createFromStoredWorkflow(WorkflowManagerInterface $wfm, $storedWorkflow);

    /**
     * Creates a Workflow array from a stored workflow list.
     *
     * @param WorkflowManagerInterface $wfm
     * @param mixed                    $storedWorkflowList
     *
     * @return array<WorkflowInterface>
     */
    public function createFromStoredWorkflowList(WorkflowManagerInterface $wfm, $storedWorkflowList);
}
