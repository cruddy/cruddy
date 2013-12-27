<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('packages/kalnoy/cruddy/css/styles.min.css') }}">
</head>
<body>
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <a href="{{ url("/") }}" class="navbar-brand">{{ $cruddy->config("title") }}</a>
        </div>

        <div class="navbar-collapse">
            {{ $cruddy->menu() }}

            <p class="navbar-text navbar-right">
                <a href="{{ url("logout") }}" type="button" class="navbar-link">@lang("cruddy::app.logout")</a>
            </p>
        </div>
    </nav>

    <div id="container" class="main-container"></div>

    <script>
    Cruddy = {{ $cruddy->toJSON() }};
    </script>

@if (Config::get("app.debug"))
    {{ HTML::script("packages/kalnoy/cruddy/js/vendor.js") }}
    {{ HTML::script("packages/kalnoy/cruddy/js/app.js") }}
@else
    {{ HTML::script("packages/kalnoy/cruddy/js/vendor.min.js") }}
    {{ HTML::script("packages/kalnoy/cruddy/js/app.min.js") }}
@endif
</body>
</html>