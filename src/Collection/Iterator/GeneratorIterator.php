<?php

declare(strict_types=1);

namespace Liberty\System\Collection\Iterator;

use Closure;
use Exception;
use Generator;
use Iterator;
use Liberty\System\Exception\DomainException;
use Liberty\System\Exception\SystemException;
use ReflectionFunction;
use Throwable;

/**
 * Class GeneratorIterator
 */
final class GeneratorIterator implements Iterator
{
    private Closure $function;
    private ?Generator $generator = null;
    private array $args;

    /**
     * Constructs GeneratorIterator
     *
     * @throws Exception
     */
    public function __construct(callable $function, array $args = [])
    {
        $reflection = new ReflectionFunction($function);
        if (!$reflection->isGenerator()) {
            throw new DomainException('Invalid Generator function');
        }
        $this->function = $function(...);
        $this->args = $args;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->generator = call_user_func_array($this->function, $this->args);
        $this->generator->rewind();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        if (!$this->generator) {
            $this->rewind();
        }

        return $this->generator->valid();
    }

    /**
     * @inheritDoc
     */
    public function key(): mixed
    {
        if (!$this->generator) {
            $this->rewind();
        }

        return $this->generator->key();
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        if (!$this->generator) {
            $this->rewind();
        }

        return $this->generator->current();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        if (!$this->generator) {
            $this->rewind();
        }

        $this->generator->next();
    }

    /**
     * Retrieves the return value of a generator.
     *
     * @throws Exception When the generator hasn't returned
     */
    public function getReturn(): mixed
    {
        if (!$this->generator) {
            $message = 'Cannot get return value; generator has not returned';
            throw new SystemException($message);
        }

        return $this->generator->getReturn();
    }

    /**
     * Sends a value to the generator.
     *
     * Sends the given value to the generator as the result of the current
     * yield expression and resumes execution of the generator.
     *
     * If the generator is not at a yield expression when this method is
     * called, it will first be let to advance to the first yield expression
     * before sending the value. As such it is not necessary to "prime" PHP
     * generators with a Generator::next() call (like it is done in Python).
     */
    public function send(mixed $value = null): mixed
    {
        if (!$this->generator) {
            $this->rewind();
        }

        return $this->generator->send($value);
    }

    /**
     * Throws an exception into the generator.
     *
     * Throws an exception into the generator and resumes execution of the
     * generator.
     *
     * The behavior will be the same as if the current yield expression was
     * replaced with a throw $exception statement.
     *
     * If the generator is already closed when this method is invoked, the
     * exception will be thrown in the caller's context instead.
     */
    public function throw(Throwable $exception): mixed
    {
        if (!$this->generator) {
            $this->rewind();
        }

        return $this->generator->throw($exception);
    }
}
