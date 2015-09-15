<?php


use Kitpages\WorkflowBundle\Model\WorkflowInterface;
use Kitpages\WorkflowBundle\Tests\CommandTestCase;
use Kitpages\WorkflowBundle\Yaml\YamlWorkflowConfigurationParser;
use Kitpages\WorkflowBundle\workflowConfiguration\WorkflowConfigurationInterface;
use Kitpages\WorkflowBundle\Manager\WorkflowManager;
use Kitpages\WorkflowBundle\Event\ActionEvent;
use Kitpages\WorkflowBundle\KitpagesWorkflowEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * WorkflowManagerTest.
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
class WorkflowManagerTest extends CommandTestCase
{
    /**
     * @var WorkflowManager
     */
    private $wm;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        $client = self::createClient();
        $this->dispatcher = $client->getContainer()->get('event_dispatcher');
        $this->wm = $client->getContainer()->get('workflow.manager');
    }

    /**
     * Tests basic Workflow inception.
     */
    public function testSubWorkflowStateChanges()
    {
        $configs = $this->getConfigList();
        $parent = $this->wm->createWorkflow('parent', $configs['parent']);
        $this->wm->addWorkflowConfiguration('hello_world', $configs['sub']);
        $this->wm->initializeWorkflow($parent);

        $this->assertEquals('intro', $parent->getCurrentState());
        $this->assertEquals('intro.start_state', $parent->getVerboseState());

        //Changing subworkflow state
        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('action_to_phpunit_step'));

        $this->assertEquals('intro', $parent->getCurrentState());
        $this->assertEquals('intro.phpunit_state', $parent->getVerboseState());

        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('hello_world_completed'));

        $this->assertEquals('middle', $parent->getCurrentState());
        $this->assertEquals('middle.start_state', $parent->getVerboseState());

        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('hello_world_completed'));

        $this->assertEquals('end', $parent->getCurrentState());
        $this->assertEquals('end', $parent->getVerboseState());
    }

    /**
     * Tests multiple Workflow inception.
     */
    public function testMultipleSubWorkflowStateChanges()
    {
        $configs = $this->getConfigList();
        $this->wm->addWorkflowConfiguration('hello_world', $configs['sub']);

        $cloneCount = 3;
        /** @var WorkflowInterface[] $workflows */
        $workflows = [];
        for ($i = 1; $i <= $cloneCount; ++$i) {
            $key = 'wf_'.$i;
            $workflows[$key] = $clone = $this->wm->createWorkflow($key, $configs['parent']);
            $this->wm->initializeWorkflow($clone);
        }

        foreach ($workflows as $key => $clone) {
            $this->assertEquals('intro', $clone->getCurrentState());
            $this->assertEquals('intro.start_state', $clone->getVerboseState());
        }

        //Changing subworkflow state
        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('action_to_phpunit_step'));

        foreach ($workflows as $key => $clone) {
            $this->assertEquals('intro', $clone->getCurrentState());
            $this->assertEquals('intro.phpunit_state', $clone->getVerboseState());
        }

        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('hello_world_completed'));

        foreach ($workflows as $key => $clone) {
            $this->assertEquals('middle', $clone->getCurrentState());
            $this->assertEquals('middle.start_state', $clone->getVerboseState());
        }

        $this->dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent('hello_world_completed'));

        foreach ($workflows as $key => $clone) {
            $this->assertEquals('end', $clone->getCurrentState());
            $this->assertEquals('end', $clone->getVerboseState());
        }
    }

    /**
     * @return WorkflowConfigurationInterface[]
     *
     * @throws Exception
     */
    private function getConfigList()
    {
        $subConfig = '
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

        $config = '
workflow_definition:
    name: parent_workflow
    init_state: intro
    state_list:
        intro:
            workflow:
                name: hello_world
            event_list:
                hello_world_completed:
                    next_state: middle
        middle:
            workflow:
                name: hello_world
            event_list:
                hello_world_completed:
                    next_state: end
        end:
            event_list:
                goto_start:
                    next_state: start_state
        ';

        return [
            'sub' => YamlWorkflowConfigurationParser::parse($subConfig),
            'parent' => YamlWorkflowConfigurationParser::parse($config),
        ];
    }
}
