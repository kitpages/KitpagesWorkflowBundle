<?php

namespace Kitpages\WorkflowBundle\Event;

class ActionEvent extends AbstractWorkflowEvent
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var \DateTime
     */
    protected $created;

    public function __construct($key)
    {
        $this->key = $key;
        $this->created = new \DateTime();
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }
}
