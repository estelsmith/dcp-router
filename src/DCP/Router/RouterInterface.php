<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
interface RouterInterface
{
     /**
     * Retrieve the components that have been attached to the router.
     * @return array
    */
    public function getComponents();

    /**
     * Attach an array of components to the router.
     * @param array $components
    */
    public function setComponents($components);

    /**
     * Retrieve the namespace prefix being applied to controllers being routed to.
     * @return string
    */
    public function getControllerPrefix();

    /**
     * Set the namespace prefix to apply to all controllers being routed to.
     * @param string $prefix
    */
    public function setControllerPrefix($prefix);

    /**
     * Dispatch URL to application controller.
     * @return mixed
    */
    public function dispatch($url);
}
