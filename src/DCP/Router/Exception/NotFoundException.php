<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Exception;

use DCP\Router\Exception;

/**
 * Exception thrown when a resource corresponding to a given URL is not found.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class NotFoundException extends \Exception implements Exception
{
}
