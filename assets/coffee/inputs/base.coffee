Cruddy.Inputs = {}

# Base class for input that will be bound to a model's attribute.
class Cruddy.Inputs.Base extends Backbone.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, (model, value, options) ->
            @applyChanges value, not options.input or options.input isnt this

        this

    # Apply changes when model's attribute changed.
    # external is true when value is changed not by input itself.
    applyChanges: (data, external) -> this

    render: -> @applyChanges @getValue(), yes

    # Focus an element.
    focus: -> this

    # Get current value.
    getValue: -> @model.get @key

    # Set current value.
    setValue: (value) ->
        @model.set @key, value, input: this

        this