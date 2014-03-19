KitpagesWorkflowBundle
======================

This bundle provides a generic workflow system.

It is used in production on a specific project, but it must be considered as an early alpha version.

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