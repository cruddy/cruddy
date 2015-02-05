Cruddy.Layout = {}

class Cruddy.Layout.Element extends Cruddy.View

    constructor: (options, parent) ->
        @parent = parent
        @disable = options.disable ? no

        super

    initialize: ->
        @model = @parent.model if not @model and @parent
        @entity = @model.entity if @model

        super

    handleValidationError: (error) ->
        @parent.handleValidationError error if @parent

        return this

    isDisabled: ->
        return yes if @disable
        return @parent.isDisabled() if @parent

        return no

    # Get whether element is focusable
    isFocusable: -> no

    # Focus the element
    focus: -> return this