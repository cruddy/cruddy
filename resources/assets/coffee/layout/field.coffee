class Cruddy.Layout.Field extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @fieldView = null

        if not @field = @entity.field options.field
            console.error "The field #{ options.field } is not found in #{ @entity.id }."

        return this

    render: ->
        if @field and @field.isVisible()
            @fieldView = @field.createView @model, @isDisabled(), this

        @$el.html @fieldView.render().$el if @fieldView

        return this

    remove: ->
        @fieldView.remove() if @fieldView

        super

    isFocusable: -> @fieldView and @fieldView.isFocusable()

    focus: ->
        @fieldView.focus() if @fieldView

        return this