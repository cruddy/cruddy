class Cruddy.Fields.Slug extends Field
    createEditableInput: (model) ->
        new Cruddy.Inputs.Slug
            model: model
            key: @id
            chars: @get "chars"
            ref: @get "ref"
            separator: @get "separator"
            attributes:
                placeholder: @get "label"

    createFilterInput: (model, column) ->
        new Cruddy.Inputs.Text
            model: model
            key: @id
            attributes:
                placeholder: @get "label"