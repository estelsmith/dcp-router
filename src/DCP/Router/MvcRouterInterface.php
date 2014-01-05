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
interface MvcRouterInterface
{
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
}