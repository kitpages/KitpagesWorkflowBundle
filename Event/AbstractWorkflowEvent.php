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

    /**
     * AbstractWorkflowEvent constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

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
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!array_key_exists($key, $this->data)) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
