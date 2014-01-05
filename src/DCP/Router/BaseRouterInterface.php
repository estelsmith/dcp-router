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
interface BaseRouterInterface
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
     * Dispatch URL to application controller.
     * @param string $url
     * @return mixed
    */
    public function dispatch($url);
}
