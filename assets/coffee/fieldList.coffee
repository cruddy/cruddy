# Displays a list of entity's fields
class FieldList extends Backbone.View
    className: "field-list"

    initialize: ->
        @listenTo @model.entity.fields, "add remove", @render

        this

    focus: ->
        @primary?.focus()

        this

    render: ->
        @$el.empty()

        @$el.append field.el for field in @createFields()

        this

    createFields: ->
        @dispose()

        @fields = (field.createView(@model).render() for field in @model.entity.fields.models)
        @primary = null

        for view in @fields when view.field.isEditable @model
            @primary = view
            break

        @fields

    dispose: ->
        field.remove() for field in @fields if @fields?

        this

    stopListening: ->
        @dispose()

        super