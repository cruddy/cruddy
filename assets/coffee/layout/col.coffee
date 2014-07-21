class Cruddy.Layout.Col extends Cruddy.Layout.BaseFieldContainer

    initialize: (options) ->
        @$el.addClass "col-xs-" + options.span

        super