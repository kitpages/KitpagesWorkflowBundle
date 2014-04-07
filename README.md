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

## State of the bundle

* beta state

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

Configuration

```yaml
kitpages_step:
    shared_step_list:
        my_step:
            help:
                short: "short description of my step"
                complete: |
                    Longer description
            class: Kitpages\MyBundle\Step\MyStep
            parameter_list:
                url: test.mydomain.com
            service_list:
                logger: logger

```

Code of the step

```php
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
        $logger = $this->getService("logger");
        $logger->info("I write a log");
        $urlStepParameter = $this->getParameter("url");


        // record some values in the workflow object
        $workflow->set("resultKey", "value calculated");

        if ($someResult == true) {
            return "ok";
        } else {
            return "false";
        }
    }
}
```

## More advanced Features

TODO : features to document

* workflow parameters
* sub workflow
* workflow events and step events
* workflow configuration shortcuts
* workflow persistance
* serveral workflows in parallel

