<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Exception;

use DCP\Router\Exception;

/**
 * Exception thrown when a method argument in the dcp-router package is not properly formed.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}
