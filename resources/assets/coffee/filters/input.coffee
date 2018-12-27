class Cruddy.Filters.Input extends Cruddy.Filters.Base
    createFilterInput: (model) -> new Cruddy.Inputs.Text
        model: model
        key: @id