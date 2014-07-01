<?php
namespace Kitpages\WorkflowBundle\Tests\QuickStart;

use Kitpages\WorkflowBundle\Tests\CommandTestCase;
use Kitpages\WorkflowBundle\Yaml\YamlWorkflowConfigurationParser;
use Kitpages\WorkflowBundle\workflowConfiguration\WorkflowConfigurationInterface;

use Kitpages\WorkflowBundle\Manager\WorkflowManager;
use Kitpages\WorkflowBundle\Manager\WorkflowManagerInterface;
use Kitpages\WorkflowBundle\Model\WorkflowInterface;

use Kitpages\WorkflowBundle\Event\ActionEvent;
use Kitpages\WorkflowBundle\KitpagesWorkflowEvents;

class ParameterTest
    extends CommandTestCase
{
    /**
     * used to know if services are good initialized
     */
    public function testHelloWorld()
    {
        $client = self::createClient();
        $config='
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
        $workflowManager = $client->getContainer()->get("workflow.manager");
        /** @var WorkflowInterface $workflow */
        $workflow = $workflowManager->createWorkflow("hello_world_instance_workflow", $workflowConfiguration);

        $workflow->set("toto", "titi");
        $this->assertEquals("titi", $workflow->get("toto"));

        $workflow->setParameter("tutu", 12);
        $this->assertEquals(12, $workflow->getParameter("tutu"));
   }
}