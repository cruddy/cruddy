<?php

namespace Kalnoy\Cruddy;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
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

        $authFilter = $this->cruddy->config('auth_filter');

        if ($authFilter) $this->beforeFilter($authFilter, ['except' => ['thumb']]);
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
        }
    }

    /**
     * Initial page.
     */
    public function index()
    {
        $dashboard = $this->cruddy->config('dashboard');

        if ( ! empty($dashboard))
        {
            if ($dashboard[0] === '@') return Redirect::route('cruddy.index', [ substr($dashboard, 1) ]);

            $this->layout->content = View::make($dashboard, ['cruddy' => $this->cruddy]);
        }
    }

    /**
     * Show an entity.
     */
    public function show()
    {
        $this->layout->content = View::make('cruddy::loading');
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