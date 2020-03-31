<?php


namespace BigBIT\SmartDI\Exceptions;


use Psr\Container\ContainerExceptionInterface;
use Throwable;

/**
 * Class CannotResolveException
 * @package BigBIT\SmartDI\Exceptions
 */
class CannotRedefinePrimitiveException extends \Exception implements ContainerExceptionInterface
{
    /**
     * CannotResolveException constructor.
     * @param string $name
     * @param string $id
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $name, string $id, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Cannot redefine primitive `%s` dependency for `%s`", $name, $id), $code, $previous);
    }

}
