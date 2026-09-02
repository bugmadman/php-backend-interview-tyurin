<?php declare(strict_types=1);

namespace App\Customer\Domain;

use App\Shared\Domain\Id\CustomerId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customers')]
class Customer
{
	public function __construct(
		#[ORM\Id]
		#[ORM\Column(type: 'customer_id')]
		public readonly CustomerId $id,
		#[ORM\Column(type: 'string', length: 255)]
		public readonly string $email,
		#[ORM\Column(type: 'string', length: 32, nullable: true)]
		public readonly string|null $country,
		#[ORM\Column(type: 'datetime_immutable')]
		public readonly \DateTimeImmutable $createdAt,
	) {
	}
}
