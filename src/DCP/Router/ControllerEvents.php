<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

/**
 * Collection of events that are called when a controller is dispatched.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class ControllerEvents
{
    const CREATING = 'dcp.router.controller.creating';
    const CREATE = 'dcp.router.controller.create';
    const CREATED = 'dcp.router.controller.created';
    const DISPATCHING = 'dcp.router.controller.dispatching';
    const DISPATCH = 'dcp.router.controller.dispatch';
    const DISPATCHED = 'dcp.router.controller.dispatched';
}