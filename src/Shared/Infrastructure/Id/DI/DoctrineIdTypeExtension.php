<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\DI;

use App\Shared\Infrastructure\Id\Doctrine\IdType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Generator;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Loaders\RobotLoader;
use ReflectionClass;

final class DoctrineIdTypeExtension extends CompilerExtension
{
	private RobotLoader $loader;

	public function __construct(
		string $appDir,
		string $cacheDir,
	) {
		$this->loader = (new RobotLoader())
			->addDirectory($appDir)
			->setTempDirectory($cacheDir);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var ServiceDefinition $connection */
		$connection = $builder->getDefinitionByType(Connection::class);

		foreach ($this->findIdClasses() as $class) {
			$php = <<<'PHP'
				(static function (): void {
					$instance = new (?)();
					if((?)::hasType($instance->getName())) {
						return;
					}

					(?)::addType($instance->getName(), $instance::class);
				})()
			PHP;

			$connection->addSetup($php, [
				$class,
				Type::class,
				Type::class,
			]);
		}
	}

	/**
	 * @return \Generator<string>
	 */
	private function findIdClasses(): Generator
	{
		foreach (\array_keys($this->loader->getIndexedClasses()) as $class) {
			if (\is_subclass_of($class, IdType::class, true)) {
				if ((new ReflectionClass($class))->isAbstract()) {
					continue;
				}

				yield $class;
			}
		}
	}
}
