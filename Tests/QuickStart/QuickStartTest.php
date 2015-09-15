<?php

namespace Kitpages\WorkflowBundle\Tests\QuickStart;

use Kitpages\WorkflowBundle\Tests\CommandTestCase;
use Kitpages\WorkflowBundle\Yaml\YamlWorkflowConfigurationParser;
use Kitpages\WorkflowBundle\workflowConfiguration\WorkflowConfigurationInterface;
use Kitpages\WorkflowBundle\Manager\WorkflowManagerInterface;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;
use Kitpages\WorkflowBundle\Event\ActionEvent;
use Kitpages\WorkflowBundle\KitpagesWorkflowEvents;

class QuickStartTest
    extends CommandTestCase
{
    /**
     * used to know if services are good initialized.
     */
    public function testHelloWorld()
    {
        $client = self::createClient();
        $config = '
workflow_definition:
    name: hello_world
    init_state: start_state
    state_list:
        start_state:
            event_list:
                goto_middle:
                    step:
                        name: workflow.default
                    next_state:
                        default: middle_state
                        fail: start_state
                cancel:
                    next_state: start_state
                goto_end:
                    next_state: end_state
        middle_state:
            event_list:
                goto_end:
                    next_state: end_state
        end_state:
            event_list:
                goto_start:
                    next_state: start_state
        ';

        $workflowConfiguration = YamlWorkflowConfigurationParser::parse($config);
        $this->assertTrue($workflowConfiguration instanceof WorkflowConfigurationInterface);

        $workflowManager = $client->getContainer()->get('workflow.manager');
        $this->assertTrue($workflowManager instanceof WorkflowManagerInterface);

        $workflow = $workflowManager->createWorkflow('hello_world_instance_workflow', $workflowConfiguration);
        $this->assertTrue($workflow instanceof WorkflowInterface);

        $this->assertEquals('start_state', $workflow->getCurrentState());

        $dispatcher = $client->getContainer()->get('event_dispatcher');
        $actionEvent = new ActionEvent('goto_end');
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, $actionEvent);
        $this->assertEquals('end_state', $workflow->getCurrentState());

        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('goto_start'));
        $this->assertEquals('start_state', $workflow->getCurrentState());
    }

    /**
     * used to know if services are good initialized.
     */
    public function testWorkflowParameterAndWorkflowValues()
    {
        $client = self::createClient();
        $config = '
workflow_definition:
    name: hello_world
    init_state: start_state
    state_list:
        start_state:
            event_list:
                action_to_phpunit_step:
                    step:
                        name: phpunit_step
                    next_state:
                        workflow_response: phpunit_state
                        fail: start_state
                        phpunit_goto_end: end_state
                cancel:
                    next_state: start_state
                goto_end:
                    next_state: end_state
        end_state:
            event_list:
                goto_start:
                    next_state: start_state
        phpunit_state:
            event_list:
                goto_start:
                    next_state: start_state
    parameter_list:
        workflow_default_response: "workflow_response"
        ';

        // init workflow and dispatcher
        $dispatcher = $client->getContainer()->get('event_dispatcher');
        $workflowConfiguration = YamlWorkflowConfigurationParser::parse($config);
        $workflowManager = $client->getContainer()->get('workflow.manager');
        $workflow = $workflowManager->createWorkflow('hello_world_instance_workflow', $workflowConfiguration);

        // check goto middle
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('action_to_phpunit_step'));
        $this->assertEquals('phpunit_state', $workflow->getCurrentState());

        // back to start and test with a given workflow parameter
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('goto_start'));
        $workflow->setParameter('workflow_default_response', 'phpunit_goto_end');
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('action_to_phpunit_step'));
        $this->assertEquals('end_state', $workflow->getCurrentState());

        // test workflow values
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('goto_start'));
        $actionEvent = new ActionEvent('action_to_phpunit_step');
        $actionEvent->set('action_event_parameter', 'test string');
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, $actionEvent);
        $this->assertEquals('test string', $workflow->get('action_event_parameter'));
    }
}
