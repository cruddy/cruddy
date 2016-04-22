class Cruddy.Inputs.DateTime extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        @format = options.format

        @$el.mask options.mask if options.mask?

        super

    handleValueChanged: (newValue, bySelf) ->
        @$el.val if newValue is null then "" else moment.unix(newValue).format @format unless bySelf

        this

    submitValue: ->
        value = @$el.val()
        value = if _.isEmpty value then null else moment(value, @format).unix()

        @setValue value

        # We will always set input value because it may not be always parsed properly
        @handleValueChanged value, yes