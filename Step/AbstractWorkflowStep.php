<?php


namespace Kitpages\WorkflowBundle\Step;


use Kitpages\StepBundle\Step\StepAbstract;
use Kitpages\StepBundle\Step\StepEvent;
use Kitpages\WorkflowBundle\Event\ActionEvent;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;

abstract class AbstractWorkflowStep extends StepAbstract
{

    const STEP_RESPONSE_DEFAULT = 'default';
    const STEP_RESPONSE_REPEAT = 'repeat';
    const STEP_RESPONSE_APPROVE = 'ok';

    /**
     * @return WorkflowInterface
     */
    public function getWorkfow(){
        return $this->getParameter('_workflow');
    }

    /**
     * @return ActionEvent
     */
    public function getActionEvent(){
        return $this->getParameter('_event');
    }

} 