# Displays a list of entity's fields
class FieldList extends Cruddy.Layout.BaseFieldContainer
    className: "field-list"

    initialize: ->
        super

        @_form = @entity.form @model

        @_form.fields.forEach (field) =>
            @append new Cruddy.Layout.Field
                field: field.id
                parent: @
                model: @model

        return this