Cruddy.Layout = {}

class Cruddy.Layout.Element extends Cruddy.View

    constructor: (options) ->
        @parent = options.parent
        @disable = options.disable ? no
        @_form = options.form ? null

        super

    initialize: ->
        @model = @model || @parent?.model
        @entity = @model?.entity

        super

    handleValidationError: (error) ->
        @parent.handleValidationError error if @parent

        return this

    isDisabled: -> @disable || @parent?.isDisabled()

    # Get whether element is focusable
    isFocusable: -> no

    # Focus the element
    focus: -> return this
        
    form: -> @_form || @parent?.form()