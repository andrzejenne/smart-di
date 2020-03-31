<?php


namespace BigBIT\SmartDI\Exceptions;


use Psr\Container\ContainerExceptionInterface;
use Throwable;

/**
 * Class CannotResolveException
 * @package BigBIT\SmartDI\Exceptions
 */
class CannotRedefineException extends \Exception implements ContainerExceptionInterface
{
    /**
     * CannotResolveException constructor.
     * @param string $id
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $id, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Cannot redefine dependency for `%s`", $id), $code, $previous);
    }

}
