<?php

namespace Kalnoy\Cruddy\Controllers;

use Intervention\Image\Exception\NotReadableException;
use Kalnoy\Cruddy\Environment;
use Redirect;
use Response;
use Input;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\Service\ThumbnailFactory;
use Whoops\Example\Exception;

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
     * @param Environment $cruddy
     * @param \Kalnoy\Cruddy\Service\ThumbnailFactory $thumb
     */
    public function __construct(Environment $cruddy, ThumbnailFactory $thumb)
    {
        $this->cruddy = $cruddy;
        $this->thumb = $thumb;
    }

    /**
     * Initial page.
     */
    public function index()
    {
        $dashboard = $this->cruddy->config('dashboard') or 'cruddy::dashboard';

       if ($dashboard[0] === '@') return Redirect::route('cruddy.index', [ substr($dashboard, 1) ]);

       return Response::view($dashboard, [ 'cruddy' => $this->cruddy ]);
    }

    /**
     * Get the schema.
     */
    public function schema()
    {
        return Response::make(app('cruddy.compiler')->schema());
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

        catch (Exception $e)
        {
            return Response::make('Failed to process image.', 404);
        }
    }
}