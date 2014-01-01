@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2.css') }}">

<input id="select" style="width: 300px" name="selected">

<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
<script>

function format(item) { return item.{{ $entity->primary_column }} }

$("#select").select2({
    ajax: {
        url: "{{ URL::route('cruddy.api.entity.search', [ $entity->getId() ]) }}",
        quietMillis: 300,

        data: function (term, page) {
            return {
                q: term,
                page: page
            };
        },

        results: function (resp) {
            return { results: resp.data.data, more: resp.data.current_page < resp.data.last_page };
        }
    },

    multiple: true,

    formatResult: format,
    formatSelection: format
});</script>
@stop