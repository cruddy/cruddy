class Cruddy.Layout.Field extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @field = @entity.field options.field

        return this

    render: ->
        @fieldView = @field.createView @model if @field and @field.isVisible()

        @$el.html @fieldView.render().$el if @fieldView

        return this

    remove: ->
        @fieldView.remove() if @fieldView

        super

    focus: ->
        @fieldView.focus() if @fieldView

        return this