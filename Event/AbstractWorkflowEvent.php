<?php

namespace Kitpages\WorkflowBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * This class is an event transmitted from steps to steps.
 */
abstract class AbstractWorkflowEvent extends Event
{
    /**
     * @var array
     */
    protected $data = array();
    /**
     * @var bool
     */
    protected $isDefaultPrevented = false;
    /**
     * @var bool
     */
    protected $isPropagationStopped = false;

    public function preventDefault()
    {
        $this->isDefaultPrevented = true;
    }

    /**
     * @return bool
     */
    public function isDefaultPrevented()
    {
        return $this->isDefaultPrevented;
    }

    public function stopPropagation()
    {
        $this->isPropagationStopped = true;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->isPropagationStopped;
    }

    /**
     * @param $key
     * @param $val
     *
     * @return $this
     */
    public function set($key, $val)
    {
        $this->data[$key] = $val;

        return $this;
    }

    /**
     * @param $key
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return;
        }

        return $this->data[$key];
    }
}
