class Cruddy.Layout.Field extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @fieldView = null
        
        if not form = @form()
            throw new Error "Cannot render field since form is not specified."

        if not @field = form.fields.get options.field
            throw new Error "The field #{ options.field } is not found."

        return this

    render: ->
        if @field?.isVisible()
            @fieldView = @field.createView @model, @isDisabled(), this

            @$el.html @fieldView.render().$el if @fieldView

        return this

    remove: ->
        @fieldView?.remove()

        super

    isFocusable: -> @fieldView?.isFocusable()

    focus: ->
        @fieldView?.focus()

        return this