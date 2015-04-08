# Displays a list of entity's fields
class FieldList extends Cruddy.Layout.BaseFieldContainer
    className: "field-list"

    initialize: ->
        super

        @append new Cruddy.Layout.Field { field: field.id }, this for field in @entity.fields.models

        return this