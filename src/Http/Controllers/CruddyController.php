<?php

namespace Kalnoy\Cruddy\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kalnoy\Cruddy\Compiler;
use Kalnoy\Cruddy\Environment;
use Illuminate\Routing\Controller;
use Kalnoy\Cruddy\Service\ThumbnailFactory;

/**
 * This controller handles base web-requests.
 *
 * @since 1.0.0
 */
class CruddyController extends Controller {

    /**
     * Initial page.
     *
     * @param Environment $cruddy
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index(Environment $cruddy)
    {
        $dashboard = config('cruddy.dashboard', 'cruddy::dashboard');

       if ($dashboard[0] === '@') return redirect()->route('cruddy.index', [ substr($dashboard, 1) ]);

       return view($dashboard, [ 'cruddy' => $cruddy ]);
    }

    /**
     * Get the schema.
     * @param Compiler $compiler
     *
     * @return Response
     */
    public function schema(Compiler $compiler)
    {
        return new JsonResponse($compiler->schema());
    }

    /**
     * Generate a thumbnail for an image.
     *
     * @param Request $input
     * @param ThumbnailFactory $factory
     *
     * @return Response
     */
    public function thumb(Request $input, ThumbnailFactory $factory)
    {
        $src = $input->get('src');
        $width = $input->get('width');
        $height = $input->get('height');

        if ($width !== null) $width = (int)$width;
        if ($height !== null) $height = (int)$height;

        try
        {
            return $factory->make(public_path($src), $width, $height)->response();
        }

        catch (Exception $e)
        {
            return response('Failed to process image.', 404);
        }
    }
}