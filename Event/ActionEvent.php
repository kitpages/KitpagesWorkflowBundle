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

    /**
     * @param array $key
     * @param array $data
     */
    public function __construct($key, array $data = array())
    {
        $this->key = $key;
        $this->created = new \DateTime();
        parent::__construct($data);
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
