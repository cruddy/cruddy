@extends('cruddy::layout')

@section('content')
    <div class="container cp-container">
        <h1 class="page-header">@yield('title')</h1>

        @yield('body')
    </div>
@stop