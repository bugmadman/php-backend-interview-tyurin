<?php declare(strict_types=1);

// Ensure bootstrap.php is loaded first to load environment variables
require_once __DIR__ . '/bootstrap.php';

return (fn () => [
		'paths' => [
			'migrations' => [
				'Database\Migration' => '%%PHINX_CONFIG_DIR%%/Migration',
			],
			'seeds' => [
				'Database\Seed' => '%%PHINX_CONFIG_DIR%%/Seed',
			],
		],
		'environments' => [
			'default_migration_table' => '_phinxlog',
			'default_environment' => 'default',
			'default' => [
				'adapter' => 'pgsql',
				'host' => 'db',
				'name' => 'app_db',
				'user' => 'app',
				'pass' => 'app',
				'port' => 5432,
				'charset' => 'utf8',
			],
		],
		'version_order' => 'creation',
	])();
