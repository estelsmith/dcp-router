<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use DCP\Router\Exception\InvalidArgumentException;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
interface BaseRouterInterface
{
    /**
     * Retrieve list of other routers that the router may dispatch.
     * @return array
     */
    public function getComponents();

    /**
     * Set list of other routers that the router may dispatch.
     * @param array $components
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setComponents($components);

    /**
     * Retrieve the namespace prefix applied to all resolved controllers.
     * @return string
     */
    public function getControllerPrefix();

    /**
     * Set the namespace prefix applied to all resolved controllers.
     * @param string $prefix
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setControllerPrefix($prefix);

    /**
     * Dispatch URL to appropriate application controller.
     * @param string|array $url
     * @throws InvalidArgumentException
     */
    public function dispatch($url);
}
