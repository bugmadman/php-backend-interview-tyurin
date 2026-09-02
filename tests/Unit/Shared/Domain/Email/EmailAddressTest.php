<?php declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Email;

use App\Shared\Domain\Email\EmailAddress;
use App\Shared\Domain\Email\Exception\EmailIsNotValid;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
	/**
	 * @throws EmailIsNotValid
	 */
	public function testAcceptsValidEmail(): void
	{
		$email = new EmailAddress('john.doe@example.com');

		self::assertSame('john.doe@example.com', $email->toString());
	}

	/**
	 * @throws EmailIsNotValid
	 */
	public function testThrowsForEmptyEmail(): void
	{
		$this->expectException(EmailIsNotValid::class);

		new EmailAddress('');
	}

	/**
	 * @throws EmailIsNotValid
	 */
	public function testThrowsForInvalidEmail(): void
	{
		$this->expectException(EmailIsNotValid::class);

		new EmailAddress('not-an-email');
	}

	/**
	 * @throws EmailIsNotValid
	 */
	public function testEqualsComparesEmailValue(): void
	{
		$a = new EmailAddress('john.doe@example.com');
		$b = new EmailAddress('john.doe@example.com');
		$c = new EmailAddress('jane.doe@example.com');

		self::assertTrue($a->equals($b));
		self::assertFalse($a->equals($c));
	}

	/**
	 * @throws EmailIsNotValid
	 */
	public function testGetDomainReturnsDomainPart(): void
	{
		self::assertSame('example.com', (new EmailAddress('john.doe@example.com'))->getDomain());
	}

	/**
	 * @throws EmailIsNotValid
	 */
	public function testGetLocalPartReturnsLocalPart(): void
	{
		self::assertSame('john.doe', (new EmailAddress('john.doe@example.com'))->getLocalPart());
	}
}
