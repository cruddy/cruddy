<?php

namespace Kalnoy\Cruddy\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class GenerateSchemaCommand extends Command {

	/**
	 * The filesystem object.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $file;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cruddy:schema';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Cruddy Entity schema.';

	/**
	 * Create a new command instance.
	 *
	 * @param Filesystem $file
	 */
	public function __construct(Filesystem $file)
	{
		parent::__construct();

		$this->file = $file;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$path = $this->getPath();
		$stub = $this->file->get(__DIR__ . '/stubs/schema.stub');
		$file = $path . '/' . $this->argument('name') . '.php';

		$this->writeSchema($file, $stub);
	}

	/**
	 * Write schema.
	 *
	 * @param string $file
	 * @param string $stub
	 *
	 * @return void
	 */
	public function writeSchema($file, $stub)
	{
		if ($this->file->exists($file))
		{
			$this->error('Schema already exists!');
		}
		else
		{
			$this->file->put($file, $this->formatStub($stub));

			$this->info('Schema created successfully!');
		}
	}

	/**
	 * Format stub.
	 *
	 * @param string $stub
	 *
	 * @return string
	 */
	public function formatStub($stub)
	{
		return strtr($stub,
		[
			'{name}' => $this->argument('name'),
			'{model}' => $this->getModel(),
			'{namespace}' => $this->getNamespace(),
		]);
	}

	/**
	 * Get path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		$path = $this->option('path');

		$path = $path ? $this->laravel['path.base'] . '/' . $path : $this->laravel['path'] . '/entities';

		if ( ! $this->file->isDirectory($path))
		{
			$this->file->makeDirectory($path, 0755, true);
		}

		return rtrim($path, '/\\');
	}

	/**
	 * Get a namespace.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		$namespace = $this->option('namespace');

		return $namespace ? "\n\nnamespace {$namespace};" : '';
	}

	/**
	 * Get a name of a model.
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->option('model') ?: $this->argument('name');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the class.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('model', null, InputOption::VALUE_OPTIONAL, 'The Eloquent model class name.', null),
			array('namespace', null, InputOption::VALUE_OPTIONAL, 'The namespace.', null),
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path.', null),
		);
	}

}
