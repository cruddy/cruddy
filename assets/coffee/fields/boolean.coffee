class Cruddy.Fields.Boolean extends Field
    createEditableInput: (model) -> new Cruddy.Inputs.Boolean { model: model, key: @id }

    createFilterInput: (model) -> new Cruddy.Inputs.Boolean { model: model, key: @id, tripleState: yes }

    format: (value) -> if value then "да" else "нет"