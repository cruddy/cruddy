<?php

namespace Kalnoy\Cruddy\Console;

use Illuminate\Console\Command;
use Kalnoy\Cruddy\Compiler;

class ClearCompiledCommand extends Command {

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
    protected $name = 'cruddy:clear-compiled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear compiled schema.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $this->compiler->clearCompiled();
    }

}