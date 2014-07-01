KitpagesWorkflowBundle
======================

[![Build Status](https://travis-ci.org/kitpages/KitpagesWorkflowBundle.svg)](https://travis-ci.org/kitpages/KitpagesWorkflowBundle)

This bundle provides a generic workflow system.

It is used in production on a specific project, but it must be considered as an beta version.

## Use case

Imagine you manage (technically) 3 different newspapers : "NYC news", "Paris news" and "Grenoble news".

* For NYC news, an article has to be validated by the editor in chief only (and rewritten by the
author until the editor says yes). Then it should be integrated to the printing process.
* For Paris news, an article is firstly validated by a secretary, then by a pair (another author from the
same domain) and the editor. Then it should be integrated to the printing process.
* For Grenoble news : This is only an online newspaper. No validation, but it can be unpublished by the
editor later

You can build a single, coherent code that can manage these 3 different buisiness processes. You can do
that through a workflow system. Each newspaper process is represented by a workflow configuration file.

This bundle provides a generic workflow system build to represent any business process.

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
* partially tested (60%)
* under travis-ci

## Installation

Add KitpagesWorkflowBundle in your composer.json

```js
{
    "require": {
        "kitpages/workflow-bundle": "~1.0"
    }
}
```

Now tell composer to download the bundle by running:

``` bash
$ php composer.phar update kitpages/workflow-bundle
```

AppKernel.php

``` php
$bundles = array(
    ...
    new Kitpages\StepBundle\KitpagesStepBundle(),
    new Kitpages\WorkflowBundle\KitpagesWorkflowBundle(),
);
```

Very minimal configuration in config.yml

```yaml
imports:
    - { resource: @KitpagesWorkflowBundle/Resources/config/steps.yml }

kitpages_workflow:
    default_step_name: workflow.default
```

## Principles

### General mecanism

* This bundle is used to manage a state machine
* The configuration of the workflow is defined in a WorkflowConfiguration object
* The current instance of a machine state is in a Workflow object
* A workflow manager keep references of every workflow, listen for ActionEvents, run steps,
change workflow states,...
* A step contains the operations to do after the reception of an ActionEvent. Then the returned value
allows to decide the next workflow state according to the configuration.
* Every workflow state listen for some ActionEvent

### Everything is done in steps

Steps are classes that does something. Steps are documented in the project
on github : [KitpagesStepBundle](https://github.com/kitpages/KitpagesStepBundle).

We are using steps that extends the AbstractWorkflowStep that add a reference to the current workflow
and the actionEvent.

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

## Versions

* 2014/04/24 : v1.0.0 - first stable release

## Roadmap

Backward compatibility is maintained for version 1.x.

By 2014/06

* more tests and docs
* yaml parser in service (static call will remain but deprecated)
* pre-generation and cache for the proxy system

Later :

* a convivial debug interface
