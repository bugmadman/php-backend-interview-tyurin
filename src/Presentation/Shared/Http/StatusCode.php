<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http;

enum StatusCode: int
{
	case SUCCESS = 200;
	case CREATED = 201;
	case NO_CONTENT = 204;
	case BAD_REQUEST = 400;
	case UNAUTHORIZED = 401;
	case FORBIDDEN = 403;
	case NOT_FOUND = 404;
	case NOT_ALLOWED = 405;
	case UNPROCESSABLE_ENTITY = 422;
	case INTERNAL_SERVER_ERROR = 500;
	case NOT_IMPLEMENTED = 501;
}
