<?php
declare(strict_types=1);

use App\Bootstrap;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';


$container = Bootstrap::bootForApi()->createContainer();
$app = $container->getByType(App::class);
$app->run();
