<!DOCTYPE html>
<html>
<head>
    <title>{{ $brand }}</title>
    {{ $cruddy->styles() }}
</head>
<body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="navbar-header">
            <a href="{{ url("/") }}" class="navbar-brand">{{ $brand }}</a>
        </div>

        <div class="navbar-collapse">
            {{ $cruddy->menu() }}

            <p class="navbar-text navbar-right">
                <a href="{{ url("logout") }}" type="button" class="navbar-link">@lang('cruddy::app.logout')</a>
            </p>
        </div>
    </nav>

    <div class="main-content" id="content">@yield('content', $content)</div>

    <script>
    Cruddy = {{ $cruddy->toJSON() }};
    </script>

    {{ $cruddy->scripts() }}
</body>
</html>