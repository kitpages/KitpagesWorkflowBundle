<?php
namespace Kitpages\WorkflowBundle\Tests\StepForTests;

use Kitpages\WorkflowBundle\Step\AbstractWorkflowStep;
use Kitpages\StepBundle\Step\StepEvent;

class PhpunitStep extends AbstractWorkflowStep
{
    public function execute(StepEvent $event = null)
    {
        $actionEvent = $this->getActionEvent();
        $workflow = $this->getWorkflow();

        $workflow->set("action_event_parameter", $actionEvent->get("action_event_parameter"));
        $response = $actionEvent->get("response");
        if (is_null($response)) {
            $response = $workflow->getParameter("workflow_default_response");
        }

        return $response;
    }
} 
