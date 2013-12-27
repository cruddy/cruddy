<?php namespace Kalnoy\Cruddy;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

class CruddyController extends Controller {

    protected $layout = 'cruddy::layouts.backend';

    protected $cruddy;

    /**
     * Initialize the controller.
     *
     * @param Environment $cruddy
     */
    public function __construct(Environment $cruddy)
    {
        $this->cruddy = $cruddy;
    }

    protected function setupLayout()
    {
        if ($this->layout !== null)
        {
            $this->layout = View::make($this->layout);
            $this->layout->title = Config::get("cruddy::title");
            $this->layout->cruddy = $this->cruddy;
        }
    }

    public function index()
    {
        $this->layout->content = "";
    }
}