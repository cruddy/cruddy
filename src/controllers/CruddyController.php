<?php namespace Kalnoy\Cruddy;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

class CruddyController extends Controller {

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
        if ($this->layout === null)
        {
            $this->layout = Config::get('cruddy::layout');
        }

        if ($this->layout !== null)
        {
            $this->layout = View::make($this->layout);

            $brand = try_trans(Config::get('cruddy::brand'));

            $this->layout->title = $brand;
            $this->layout->brand = $brand;
            $this->layout->cruddy = $this->cruddy;
            $this->layout->assets = Config::get('cruddy::assets');
        }
    }

    public function index()
    {
        $this->layout->content = "";
    }
}