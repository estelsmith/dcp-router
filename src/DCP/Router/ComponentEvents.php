<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

/**
 * Collection of events that are called when a component is dispatched.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class ComponentEvents
{
    const CREATING = 'dcp.router.component.creating';
    const CREATE = 'dcp.router.component.create';
    const CREATED = 'dcp.router.component.created';
    const DISPATCHING = 'dcp.router.component.dispatching';
    const DISPATCH = 'dcp.router.component.dispatch';
    const DISPATCHED = 'dcp.router.component.dispatched';
}
