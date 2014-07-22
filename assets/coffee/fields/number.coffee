class Cruddy.Fields.Number extends Cruddy.Fields.Input

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id