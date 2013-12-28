<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset("{$assets}/css/styles.min.css") }}">
</head>
<body>
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <a href="{{ url("/") }}" class="navbar-brand">{{ $brand }}</a>
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
    {{ HTML::script("{$assets}/js/vendor.js") }}
    {{ HTML::script("{$assets}/js/app.js") }}
@else
    {{ HTML::script("{$assets}/js/vendor.min.js") }}
    {{ HTML::script("{$assets}/js/app.min.js") }}
@endif
</body>
</html>