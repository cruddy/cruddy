class Cruddy.fields.Slug extends Field
    createEditableInput: (model) ->
        new SlugInput
            model: model
            key: @id
            chars: @get "chars"
            ref: @get "ref"
            separator: @get "separator"
            attributes:
                placeholder: @get "label"

    createFilterInput: (model, column) ->
        new TextInput
            model: model
            key: @id
            attributes:
                placeholder: @get "label"