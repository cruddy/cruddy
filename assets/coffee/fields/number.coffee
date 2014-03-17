class Cruddy.Fields.Number extends Cruddy.Fields.Base
    createEditableInput: (model) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        attributes:
            type: "text"

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id