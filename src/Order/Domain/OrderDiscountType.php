<?php declare(strict_types=1);

namespace App\Order\Domain;

enum OrderDiscountType: string
{
	case PERCENT = 'percent';
	case FIXED = 'fixed';
}
