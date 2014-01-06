<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use DCP\Router\Exception\NotFoundException;

/**
 * Provides a minimalistic event-based REST router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class RestRouter extends BaseRouter
{
    /**
     * @var string
     */
    protected $method;

    /**
     * {@inheritdoc}
     * @param string $method
     */
    public function dispatch($url, $method = 'get')
    {
        $this->method = strtolower($method);
        parent::dispatch($url);
    }

    /**
     * {@inheritdoc}
     */
    protected function setupControllerListeners()
    {
        parent::setupControllerListeners();
        $this->setupControllerDispatchingListener();
    }

    /**
     * Add default event listener for ControllerEvents::DISPATCHING event.
     */
    protected function setupControllerDispatchingListener()
    {
        $this->on(ControllerEvents::DISPATCHING, function (Event\Controller\DispatchEvent $event) {
            $controller = $event->getController();
            $method = $this->method;

            if (!method_exists($controller, $method)) {
                $className = get_class($controller);

                throw new NotFoundException('Could not find ' . $className . '::' . $method);
            } else {
                $event->setMethod($method);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function setupComponentListeners()
    {
        parent::setupComponentListeners();

        $this->removeAllListeners(ComponentEvents::DISPATCH);
        $this->on(ComponentEvents::DISPATCH, function (Event\Component\DispatchEvent $event) {
            $component = $event->getComponent();
            $component->dispatch($event->getUrl(), $this->method);
        });
    }
}
