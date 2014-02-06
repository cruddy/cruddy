<?php namespace Kalnoy\Cruddy;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Intervention\Image\Exception\ImageNotFoundException;
use Kalnoy\Cruddy\Service\ThumbnailFactory;

class CruddyController extends Controller {

    /**
     * @var \Kalnoy\Cruddy\Environment
     */
    protected $cruddy;

    /**
     * @var ThumbnailFactory
     */
    protected $thumb;

    /**
     * Initialize the controller.
     *
     * @param \Kalnoy\Cruddy\Service\ThumbnailFactory $thumb
     */
    public function __construct(ThumbnailFactory $thumb)
    {
        $this->cruddy = app('cruddy');
        $this->thumb = $thumb;

        $this->beforeFilter('cruddy.auth', ['only' => ['index']]);
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

            $this->layout->brand = $this->layout->title = \Kalnoy\Cruddy\try_trans($this->cruddy->config('brand'));
            $this->layout->cruddy = $this->cruddy;
            $this->layout->assets = $this->cruddy->config('assets');
        }
    }

    /**
     * Initial page.
     */
    public function index()
    {
        $this->layout->content = '';
    }

    /**
     * Generate a thumbnail for an image.
     *
     * @return \Illuminate\Http\Response
     */
    public function thumb()
    {
        $src = Input::get('src');
        $width = Input::get('width');
        $height = Input::get('height');

        try
        {
            return $this->thumb->make(public_path().$src, $width, $height)->response();
        }

        catch (ImageNotFoundException $e)
        {
            App::abort(404);
        }
    }
}