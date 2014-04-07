KitpagesWorkflowBundle
======================

This bundle provides a generic workflow system.

It is used in production on a specific project, but it must be considered as an beta version.

The api will change

## Quick start

* add default step in config.yml

```yaml
kitpages_workflow:
    default_step_name: workflow.default

kitpages_step:
    shared_step_list:
        workflow.default:
            help:
                short: "Default step"
                complete: |
                    This step always return default value
            class: Kitpages\WorkflowBundle\Step\DefaultStep
```

* using

```php
        // create workflow configuration
        $config='
workflow_definition:
    name: hello_world
    init_state: start_state
    state_list:
        start_state:
            event_list:
                goto_end:
                    step:
                        name: workflow.default
                    next_state:
                        default: end_state
                cancel:
                    next_state: start_state
        end_state:
            event_list:
                goto_start:
                    next_state: start_state
        ';

        // get workflow manager
        $workflowManager = $this->get("workflow.manager");

        // create workflow
        $workflowConfiguration = YamlWorkflowConfigurationParser::parse($config);
        $workflow = $workflowManager->createWorkflow("hello_world_instance_workflow", $workflowConfiguration);

        // dispatch event
        $dispatcher = $this->get('event_dispatcher');
        $actionEvent = new ActionEvent("goto_end");
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, $actionEvent);

        // test workflow
        $this->assertEquals ( "end_state", $workflow->getCurrentState() );

        // back to start
        $dispatcher->dispatch(KitpagesWorkflowEvents::ACTION_EVENT, new ActionEvent("goto_start"));
        $this->assertEquals ( "start_state", $workflow->getCurrentState() );
```

## Principles

### General mecanism

* This bundle is used to manage a state machine
* for a given state, an event (a symfony2 event) runs a step
* this step does whatever you want and returns a code
* according to the code returned and the conf of the framework, the next state is chosen

### Everything is done in steps

Steps are classes that does something. Steps are documented in the project
on github : [KitpagesStepBundle](https://github.com/kitpages/KitpagesStepBundle).

We are using steps that extends the AbstractWorkflowStep that add a reference to the current workflow and the actionEvent.

Example of step :

```php
<?php
use Kitpages\StepBundle\Step\StepEvent;
use Kitpages\WorkflowBundle\Step\AbstractWorkflowStep;

class MyStep extends AbstractWorkflowStep {

    public function execute(StepEvent $event = null)
    {
        // get current workflow and action event
        $workflow = $this->getWorkflow();
        $actionEvent = $this->getActionEvent();

        // extract data from actionEvent
        $myValue1 = $actionEvent->get("myKey");
        $myOtherValue = $actionEvent->get("myOtherKey");

        // do someting


        // record some values in the workflow object
        $workflow->set("resultKey", "value calculated");

        if ($someResult == true) {
            return "ok";
        } else {
            return "false";
        }
    }
}
?>
```


## state of the bundle

It is for the moment :

* not documented
* not tested
* not stable

Disclamer : view this bundle as a proof of concept

# how to register a workflow

tag workflow.provider with an alias to add to a service. This service should implement the
WorkflowProviderInterface.

# Configuration Example

```yaml
workflow_definition:
    name: experiment_xyz
    init_state: start
    state_list:
        start:
            event_list:
                click_ok:
                    step:
                        name: simple_step
                        parameter_list:
                            key: value
                    next_state:
                        ok: xyz_game1
                        fail: start
                click_cancel:
                    next_state: start
        xyz_game1:
            workflow:
                name: xyz_game
                parameter_list:
                    title: %xyz_game1_title%
            event_list:
                xyz_ended:
                    step:
                        name: iteration_counter
                        parameter_list:
                            max_interation_count: %xyz_game_max_iteration_count%
                    next_state:
                        repeat: xyz_game1
                        finished: waiting_state1
        waiting_state3

        xyz_game2:
            workflow:
                name: xyz_game
            event_list:
                xyz_ended:
                    step:
                        name: iteration_counter
                        parameter_list:
                            max_interation_count: %xyz_game_max_iteration_count%
                    next_state:
                        repeat: xyz_game1
                        finished: waiting_state1

    parameter_list:
        xyz_game_max_iteration_count: 2
        xyz_game1_title: "Choose your square"
        xyz_game2_title: "Choose your circle"
```

# Step Configurations

Steps are provided by a YAML file : Resources/config/steps.yml in your bundle.

Do not forget to add it in the imports in app/config/module_steps.yml

```yaml
- { resource: @KitpagesWorkflowBundle/Resources/config/steps.yml }
```