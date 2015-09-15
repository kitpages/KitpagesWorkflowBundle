<?php

namespace Kitpages\WorkflowBundle\Event;

use Kitpages\WorkflowBundle\Model\WorkflowInterface;

class StateChangeEvent extends AbstractWorkflowEvent
{
    /**
     * @var WorkflowInterface
     */
    protected $workflow;

    /**
     * @param WorkflowInterface $workflow
     *
     * @return $this
     */
    public function setWorkflow(WorkflowInterface $workflow)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * @return WorkflowInterface
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }
}
