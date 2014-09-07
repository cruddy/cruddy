<?php

namespace Kalnoy\Cruddy\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Exception\ImageNotFoundException;
use Intervention\Image\Exception\RuntimeException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\Service\ThumbnailFactory;
use Log;

/**
 * This controller handles base web-requests.
 *
 * @since 1.0.0
 */
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
     * @param Environment                             $cruddy
     * @param \Kalnoy\Cruddy\Service\ThumbnailFactory $thumb
     */
    public function __construct(Environment $cruddy, ThumbnailFactory $thumb)
    {
        $this->cruddy = $cruddy;
        $this->thumb = $thumb;

        $authFilter = $this->cruddy->config('auth_filter');

        if ($authFilter) $this->beforeFilter($authFilter, [ 'except' => 'thumb' ]);
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

            return view($dashboard, [ 'cruddy' => $this->cruddy ]);
        }
    }

    /**
     * Show an entity.
     */
    public function show()
    {
        return view('cruddy::loading');
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

        if ($width !== null) $width = (int)$width;
        if ($height !== null) $height = (int)$height;

        try
        {
            return $this->thumb->make(public_path($src), $width, $height)->response();
        }

        catch (RuntimeException $e)
        {
            Log::error($e);

            App::abort(404, $e->getMessage());
        }
    }
}