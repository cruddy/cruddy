<?php

namespace Kalnoy\Cruddy\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Kalnoy\Cruddy\Compiler;

class CompileCommand extends Command {

    /**
     * The filesystem object.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $compiler;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cruddy:compile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile a schema for quicker access.';

    /**
     * Create a new command instance.
     *
     * @param Compiler $compiler
     */
    public function __construct(Compiler $compiler)
    {
        parent::__construct();

        $this->compiler = $compiler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->compiler->compile($this->argument('locale'));

        $this->info('All done!');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('locale', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The locale id.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
