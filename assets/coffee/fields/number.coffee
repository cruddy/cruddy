class Cruddy.Fields.Number extends Cruddy.Fields.Base
    createEditableInput: (model, inputId) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        attributes:
            type: "text"
            id: inputId

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id