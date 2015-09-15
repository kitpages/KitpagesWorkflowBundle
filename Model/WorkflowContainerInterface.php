<?php

namespace Kitpages\WorkflowBundle\Model;

/**
 * Class WorkflowContainerInterface.
 */
interface WorkflowContainerInterface
{
    /**
     * @return WorkflowInterface
     */
    public function getWorkflow();

    /**
     * @param WorkflowInterface $workflow
     *
     * @return WorkflowContainerInterface
     */
    public function setWorkflow($workflow);
}
