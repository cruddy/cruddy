# Displays a list of entity's fields
class FieldList extends Backbone.View
    className: "field-list"

    initialize: (options) ->
        @forceDisable = options.forceDisable ? false

        this

    # Focus first editable field
    focus: ->
        @primary?.focus()

        this

    render: ->
        @dispose()

        @$el.empty()
        @$el.append field.el for field in @createFields()

        this

    createFields: ->
        @fields = (field.createView(@model, @forceDisable).render() for field in @model.entity.fields.models when field.isVisible())

        for view in @fields when view.isEditable
            @primary = view
            break

        @fields

    dispose: ->
        field.remove() for field in @fields if @fields?

        @fields = null
        @primary = null

        this

    remove: ->
        @dispose()

        super