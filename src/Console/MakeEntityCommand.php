<?php

namespace Kalnoy\Cruddy\Console;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class MakeEntityCommand extends GeneratorCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:entity';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Cruddy entity';

    /**
     * @var string
     */
    protected $type = 'Entity';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/schema.stub';
    }

    /**
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Entities';
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        return $this->replaceModel(parent::buildClass($name), $name);
    }

    /**
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceModel($stub, $name)
    {
        $rootNamespace = trim($this->laravel->getNamespace(), '\\');

        return str_replace('DummyModel', $rootNamespace.'\\'.class_basename($name), $stub);
    }

}
