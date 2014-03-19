namespace <<proxyNameSpace>>;

    use Kitpages\WorkflowBundle\Proxy\ProxyInterface;
    use Kitpages\WorkflowBundle\Event\StateChangeEvent;
    use Kitpages\WorkflowBundle\KitpagesWorkflowEvents;
    use Kitpages\WorkflowBundle\Model\WorkflowInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    /**
    * This class is a proxy around a workflow object.
    * This proxy adds the following methods :
    * -
    *
    * @example
    */
    class <<shortClassName>>
        extends <<originalClassName>>
            implements ProxyInterface, WorkflowInterface
            {
                ////
                // overidden methods
                ////

                /**
                * @param string $state
                * @return $this
                */
                public function setCurrentState($state, $disableEvent=false)
                {
                    if(!$disableEvent){
                        $event = new StateChangeEvent();
                        $event->setWorkflow($this);
                        $event->set("nextState", $state);
                        $this->__workflowProxyEventDispatcher->dispatch(KitpagesWorkflowEvents::ON_STATE_CHANGE, $event);

                        if (!$event->isDefaultPrevented()) {
                            parent::setCurrentState( $event->get("nextState") );
                        }
                        $this->__workflowProxyEventDispatcher->dispatch(KitpagesWorkflowEvents::AFTER_STATE_CHANGE, $event);
                    }else{
                        parent::setCurrentState( $state );
                    }
                    return $this;
                }


                ////
                // added methods
                ////
                private $__workflowProxyEventDispatcher = null;
                public function __workflowProxySetEventDispatcher(EventDispatcherInterface $dispatcher)
                {
                    $this->__workflowProxyEventDispatcher = $dispatcher;
                }
            }
