<?php

declare(strict_types=1);

namespace Liberty\System\Test\Exception;

use Liberty\System\Exception\ErrorException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ErrorException::class)]
class ErrorExceptionTest extends UnitTestCase
{
    public function test_that_get_errors_returns_array_of_errors(): void
    {
        $errors = ['email_address' => 'ljenkins@example.com is already taken'];
        $exception = new ErrorException('Validation Errors', $errors);
        $validationErrors = $exception->getErrors();

        static::assertSame($errors['email_address'], $validationErrors['email_address']);
    }
}
