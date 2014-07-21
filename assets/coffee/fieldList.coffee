# Displays a list of entity's fields
class FieldList extends Cruddy.Layout.BaseFieldContainer
    className: "field-list"

    initialize: ->
        super

        @field field: field.id for field in @entity.fields.models

        return this