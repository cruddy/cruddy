<?php

namespace Kalnoy\Cruddy\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Exception\RuntimeException;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\Compiler;
use Kalnoy\Cruddy\Service\ThumbnailFactory;
use Log;

/**
 * This controller handles base web-requests.
 *
 * @since 1.0.0
 */
class CruddyController {

    /**
     * @var Environment
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
     * @param ThumbnailFactory $thumb
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
        $dashboard = $this->cruddy->config('dashboard');

        if ( ! empty($dashboard))
        {
            if ($dashboard[0] === '@') return Redirect::route('cruddy.index', [ substr($dashboard, 1) ]);

            return view($dashboard, [ 'cruddy' => $this->cruddy ]);
        }
    }

    /**
     * Get the schema.
     *
     * @param Compiler $compiler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function schema(Compiler $compiler)
    {
        return response($compiler->schema());
    }

    /**
     * Generate a thumbnail for an image.
     *
     * @return \Illuminate\Http\Response
     */
    public function thumb(Request $input)
    {
        $src = $input->get('src');
        $width = $input->get('width');
        $height = $input->get('height');

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