class Cruddy.Inputs.DateTime extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        @format = options.format

        super

    applyChanges: (value, external) ->
        @$el.val moment.unix(value).format @format if external

        this

    change: ->
        @setValue value = moment(@$el.val(), @format).unix()

        # We will always set input value it may not be parsed properly
        @applyChanges value, yes