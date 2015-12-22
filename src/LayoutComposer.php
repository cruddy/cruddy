<?php

namespace Kalnoy\Cruddy;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\View;
use Kalnoy\Cruddy\Service\MenuBuilder;

class LayoutComposer
{
    /**
     * @var Environment
     */
    protected $cruddy;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var MenuBuilder
     */
    protected $menuBuilder;

    /**
     * @var Assets
     */
    protected $assets;

    /**
     * @param Environment $cruddy
     * @param UrlGenerator $url
     * @param Request $request
     * @param MenuBuilder $menuBuilder
     * @param Assets $assets
     */
    public function __construct(Environment $cruddy, UrlGenerator $url,
                                Request $request, MenuBuilder $menuBuilder,
                                Assets $assets
    ) {
        $this->cruddy = $cruddy;
        $this->url = $url;
        $this->request = $request;
        $this->menuBuilder = $menuBuilder;
        $this->assets = $assets;
    }

    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->cruddyData = $this->cruddy->data();

        $view->cruddyData += [
            'schemaUrl' => $this->url->route('cruddy.schema'),
            'thumbUrl' => $this->url->route('cruddy.thumb'),
            'baseUrl' => $this->url->route('cruddy.home'),
            'root' => $this->request->root(),
            'token' => csrf_token(),
        ];

        $view->scripts = $this->assets->scripts();
        $view->styles = $this->assets->styles();

        $view->menu = $this->menuBuilder;
    }
}