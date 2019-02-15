<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', $cruddyData['brandName'])</title>
    {!! $styles !!}
</head>
<body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <a href="{!! url(config('cruddy.brand_url', '/')) !!}" target="_blank" class="navbar-brand">
                    {{ $cruddyData['brandName'] }}
                </a>
            </div>

            <div class="collapse navbar-collapse" id="navbar-collapse">
                {!! $menu->render(config('cruddy.menu', []), [ 'class' => 'nav navbar-nav' ]) !!}
                {!! $menu->render(config('cruddy.service_menu', []), [ 'class' => 'nav navbar-nav navbar-right']) !!}
            </div>
        </div>
    </nav>

    <div class="main-content" id="content">
        @yield('content')
    </div>

    <script>
    Cruddy = {!! json_encode($cruddyData) !!};
    </script>

    {!! $scripts !!}
</body>
</html>