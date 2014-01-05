<?php namespace Kalnoy\Cruddy;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;

class CruddyController extends Controller {

    /**
     * @var Environment
     */
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

    /**
     * @inheritdoc
     */
    protected function setupLayout()
    {
        if ($this->layout === null)
        {
            $this->layout = $this->cruddy->config('layout');
        }

        if ($this->layout !== null)
        {
            $this->layout = View::make($this->layout);

            $this->layout->brand = $this->layout->title = try_trans($this->cruddy->config('brand'));
            $this->layout->cruddy = $this->cruddy;
            $this->layout->assets = $this->cruddy->config('assets');
        }
    }

    /**
     * Initial page.
     */
    public function index()
    {
        $this->layout->content = "";
    }
}