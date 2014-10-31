<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', $brand)</title>
    {{ $styles }}
</head>
<body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="{{ $brand_url }}" class="navbar-brand">{{ $brand }}</a>
            </div>

            <div class="navbar-collapse">
                {{ $mainMenu }}
                {{ $serviceMenu }}
            </div>
        </div>
    </nav>

    <div class="main-content" id="content">
        @yield('content')
    </div>

    <script>
    Cruddy = {{ json_encode($cruddyData) }};
    </script>

    {{ $scripts }}
</body>
</html>