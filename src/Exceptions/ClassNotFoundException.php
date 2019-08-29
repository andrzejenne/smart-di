<?php


namespace BigBIT\SmartDI\Exceptions;


use Psr\Container\ContainerExceptionInterface;
use Throwable;

/**
 * Class CannotResolveException
 * @package BigBIT\SmartDI\Exceptions
 */
class ClassNotFoundException extends \Exception implements ContainerExceptionInterface
{
    /**
     * CannotResolveException constructor.
     * @param string $id
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($id = "", $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Class %s not exists"
            . (!empty($message) ? ", $message" : ""), $id), $code, $previous);
    }

}
