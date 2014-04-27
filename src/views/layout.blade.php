<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $brand }}</title>
    {{ $cruddy->styles() }}
</head>
<body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="{{ $brand_url }}" class="navbar-brand">{{ $brand }}</a>
            </div>
            
            <div class="navbar-collapse">
                {{ $menu->render($mainMenu) }}
            
                @if ($serviceMenu)
                    {{ $menu->render($serviceMenu, 'nav navbar-nav navbar-right') }}
                @endif
            </div>
        </div>
    </nav>

    <div class="main-content" id="content">@yield('content', $content)</div>

    <script>
    Cruddy = {{ $cruddyJSON }};
    </script>

    {{ $cruddy->scripts() }}
</body>
</html>