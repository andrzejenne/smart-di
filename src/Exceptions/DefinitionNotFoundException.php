<?php


namespace BigBIT\SmartDI\Exceptions;


use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Class DefinitionNotFoundException
 * @package BigBIT\SmartDI\Exeptions
 */
class DefinitionNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * DefinitionNotFoundException constructor.
     * @param string $id
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $id, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf("Definition for %s not found", $id), $code, $previous);
    }


}
