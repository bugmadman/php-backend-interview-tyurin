<?php declare(strict_types=1);

namespace App\Shared\Domain\Email;

use App\Shared\Domain\Email\Exception\EmailIsNotValid;
use App\Shared\Domain\Exception\LogicException;
use Nette\Utils\Validators;

final readonly class EmailAddress implements \Stringable
{
	private string $email;

	/**
	 * @throws EmailIsNotValid
	 */
	public function __construct(string $email)
	{
		if ($email === '') {
			throw EmailIsNotValid::createForEmpty();
		}

		if (Validators::isEmail($email) === false) {
			throw EmailIsNotValid::create($email);
		}

		$this->email = $email;
	}

	public function equals(EmailAddress $emailAddress): bool
	{
		return $this->email === $emailAddress->email;
	}

	public function getDomain(): string
	{
		/** @var array<string> $explodedEmail */
		$explodedEmail = \explode('@', $this->email);
		$domain = \array_pop($explodedEmail);

		if (\is_string($domain)) {
			return $domain;
		}

		throw LogicException::createForNullValue('domain');
	}

	public function getLocalPart(): string
	{
		/** @var array<string> $explodedEmail */
		$explodedEmail = \explode('@', $this->email);
		$localPart = \array_shift($explodedEmail);

		if (\is_string($localPart)) {
			return $localPart;
		}

		throw LogicException::createForNullValue('local part');
	}

	public function toString(): string
	{
		return $this->email;
	}

	public function __toString(): string
	{
		return $this->toString();
	}
}
