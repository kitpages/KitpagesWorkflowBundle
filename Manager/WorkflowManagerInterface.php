<?php

namespace Kitpages\WorkflowBundle\Manager;

use Kitpages\WorkflowBundle\WorkflowConfiguration\WorkflowConfigurationInterface;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;

interface WorkflowManagerInterface
{
    /**
     * @param $key
     * @param WorkflowConfigurationInterface $workflowConfiguration
     * @param array                          $parameterList
     * @param WorkflowInterface              $parentWorkflow
     *
     * @return mixed
     */
    public function createWorkflow($key, WorkflowConfigurationInterface $workflowConfiguration, array $parameterList = [], WorkflowInterface $parentWorkflow = null);

    /**
     * Returns a non-managed Workflow instance.
     *
     * @return WorkflowInterface
     */
    public function createEmptyWorkflow();

    /**
     * @return array of WorkflowInterface
     */
    public function getWorkflowList();

    /**
     * @return array of WorkflowConfigurationInterface
     */
    public function getWorkflowConfigurationList();

    /**
     * @param $key
     *
     * @return WorkflowInterface
     */
    public function getWorkflow($key);

    /**
     * @param $key
     *
     * @return WorkflowConfigurationInterface
     */
    public function getWorkflowConfiguration($key);

    /**
     * A storable workflow list.
     *
     * @return mixed
     */
    public function getStorableWorkflowList();

    /**
     * @param mixed $storableWorkflowList
     *
     * @return WorkflowManager
     */
    public function createWorkflowListFromStorable($storableWorkflowList);
}
