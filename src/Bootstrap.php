<?php declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator();
		$appDir = \dirname(__DIR__);
		$defaultExtensions = $configurator->defaultExtensions;
		unset($defaultExtensions['inject'], $defaultExtensions['session'], $defaultExtensions['http']);
		$configurator->defaultExtensions = $defaultExtensions;

		$configurator->setDebugMode(true);
		$configurator->enableTracy($appDir . '/temp/log');

		$configurator->setTimeZone('UTC');
		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->addConfig($appDir . '/src/config/config.neon');

		return $configurator;
	}

	public static function bootForApi(): Configurator
	{
		$configurator = self::boot();
		$appDir = \dirname(__DIR__);

		$configurator->addConfig($appDir . '/src/Presentation/API/config/config.neon');

		return $configurator;
	}
}
