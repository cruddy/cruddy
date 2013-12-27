class Cruddy.fields.Boolean extends Field
    createEditableInput: (model) -> new BooleanInput { model: model, key: @id }

    createFilterInput: (model) -> new BooleanInput { model: model, key: @id, tripleState: yes }

    format: (value) -> if value then "да" else "нет"

Cruddy.fields.register "Boolean", Cruddy.fields.Boolean