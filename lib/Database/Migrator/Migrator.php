<?php

namespace AOD\Plugin\Database\Migrator;

use AOD\Plugin\Support\Helpers\FileSystemHelper;
use AOD\Plugin\Support\Traits\Database\HasConnectionTrait;
use AOD\Plugin\Support\Traits\Database\HasTableNamesTrait;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

class Migrator implements ContainerAwareInterface
{
    use ContainerAwareTrait,
        HasConnectionTrait,
        HasTableNamesTrait;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var
     */
    protected $migrations;

    /**
     * Migrator constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->repository = new Repository();
        $this->repository->checkOrCreateRepository();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function run()
    {
        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $files = $this->getMigrationFiles();

        $this->requireMigrationFiles($migrations = $this->getPendingMigrations(
            $files, $this->repository->getRanMigrations()
        ));

        // Once we have all the migrations that are outstanding we are ready to run
        $this->runPending($migrations);

        return $migrations;
    }

    /**
     * @param array $migrations
     *
     * @throws Exception
     */
    public function runPending($migrations)
    {
        if (count($migrations) === 0) {
            return;
        }

        $batch = $this->repository->getNextBatchNumber() + 1;

        foreach ($migrations as $migration) {
            $this->runUp($migration, $batch);
        }
    }

    /**
     * @param $file
     * @param $batch
     *
     * @throws Exception
     */
    public function runUp($file, $batch)
    {
        $migration = $this->resolve(
            $name = $this->getMigrationName($file)
        );

        $this->runMigration($migration, 'up');
        $this->repository->log($name, $batch);
    }

    /**
     * @param AbstractMigration $migration
     * @param string $method
     *
     * @throws Exception
     */
    public function runMigration($migration, $method)
    {
        $callback = function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        };

        $connection = $this->getConnection();
        $connection->getSchemaGrammar()->supportsSchemaTransactions() && $migration->withinTransaction
            ? $connection->transaction($callback)
            : $callback();
    }

    /**
     * @param $file
     *
     * @return AbstractMigration
     */
    public function resolve($file)
    {
        $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));
        $class = $this->getMigrationsNamespace() . $class;

        return $this->getContainer()->get($class);
    }

    /**
     * @return array
     */
    public function getMigrationFiles()
    {
        if (empty($this->migrations)) {
            $this->migrations = Collection::make(
                FileSystemHelper::getFilesFromDirectoryRecursive(
                    $this->getMigrationsPath(),
                    [ '.', '..', '.gitkeep' ]
                )
            )->sortBy('migration')->values()->toArray();
        }

        return $this->migrations;
    }

    /**
     * @param $migrations
     * @param $ran
     *
     * @return array
     */
    public function getPendingMigrations($migrations, $ran)
    {
        return Collection::make($migrations)
            ->reject(function ($migration) use ($ran) {
                return in_array($this->getMigrationName($migration), $ran);
            })->values()->all();
    }

    /**
     * @param $migration
     *
     * @return string
     */
    protected function getMigrationName($migration)
    {
        return str_replace('.php', '', basename($migration));
    }

    /**
     * @param $file
     *
     * @return string
     */
    protected function getMigrationFilePath($file)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, trailingslashit($this->getMigrationsPath()) . $file);
    }

    /**
     * @param $migration
     *
     * @return string|string[]
     */
    protected function getMigrationClassName($migration)
    {
        $base_name = $this->getMigrationName($migration);

        return str_replace($base_name, Str::studly(substr($base_name, 18)), $migration);
    }

    /**
     * @return string
     */
    protected function getMigrationsPath()
    {
        return $this->container->get('config')->get('paths.lib_path') . '/Database/Migrations';
    }

    /**
     * @return string
     */
    protected function getMigrationsNamespace()
    {
        return "AOD\\Plugin\\Database\\Migrations\\";
    }

    /**
     * Includes all of the necessary files for each migration
     * @param array $migrations
     */
    protected function requireMigrationFiles($migrations)
    {
        Collection::make($migrations)
            ->map(function ($file) {
                return $this->getMigrationFilePath($file);
            })
            ->map(function ($file) {
                require_once $file;
            });
    }
}
