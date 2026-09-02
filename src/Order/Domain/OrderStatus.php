<?php declare(strict_types=1);

namespace App\Order\Domain;

enum OrderStatus: string
{
	case DRAFT = 'draft';
	case CONFIRMED = 'confirmed';
	case CANCELLED = 'cancelled';
}
