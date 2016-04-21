class Cruddy.Fields.Text extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) -> new Cruddy.Inputs.Textarea
        model: model
        key: @id
        attributes:
            placeholder: @attributes.placeholder
            id: inputId
            rows: @attributes.rows

    getType: -> "text"