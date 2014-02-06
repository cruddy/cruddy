class Cruddy.Columns.Computed extends Cruddy.Columns.Base

    createFilter: (model) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        attributes:
            placeholder: @attributes.header

    getClass: -> super + " col-computed"