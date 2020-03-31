<?php


namespace BigBIT\SmartDI\Exceptions;


use Psr\Container\ContainerExceptionInterface;
use Throwable;

/**
 * Class DependencyException
 * @package BigBIT\SmartDI\Exceptions
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
    /**
     * DependencyException constructor.
     * @param string $reason
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($reason = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($reason, $code, $previous);
    }

}
