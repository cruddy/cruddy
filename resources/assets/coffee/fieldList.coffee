# Displays a list of entity's fields
class FieldList extends Cruddy.Layout.BaseFieldContainer
    className: "field-list"

    initialize: ->
        super

        for field in @entity.fields.models
            @create { class: "Field", field: field.id }

        return this