<?php


namespace Kitpages\WorkflowBundle\Step;


use Kitpages\StepBundle\Step\StepEvent;

/**
 * Class DefaultStep
 * This step executes when next_state is directly specified in workflow configuration
 *
 */
class DefaultStep extends AbstractWorkflowStep {

    public function execute(StepEvent $event = null)
    {
        return self::STEP_RESPONSE_DEFAULT;
    }
} 