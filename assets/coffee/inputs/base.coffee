Cruddy.Inputs = {}

# Base class for input that will be bound to a model's attribute.
class BaseInput extends Backbone.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, @applyChanges

        this

    # Apply changes when model's attribute changed.
    applyChanges: (model, data) -> this

    render: -> @applyChanges @model, @model.get @key

    focus: -> this