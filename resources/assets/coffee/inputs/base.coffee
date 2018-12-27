Cruddy.Inputs = {}

# Base class for input that will be bound to a model's attribute.
class Cruddy.Inputs.Base extends Cruddy.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, (model, value, { input }) ->
            @handleValueChanged value, input is this

        this

    # Apply changes when model's attribute changed.
    # external is true when value is changed not by input itself.
    handleValueChanged: (newValue, bySelf) -> this

    render: -> @handleValueChanged @getValue(), no

    # Focus an element.
    focus: -> this

    # Get current value.
    getValue: -> @model.get @key

    # Set current value.
    setValue: (value, options = {}) ->
        options.input = this unless options.hasOwnProperty('input')

        @model.set @key, value, options

        this

    emptyValue: -> null

    empty: -> @setValue @emptyValue(), input: null