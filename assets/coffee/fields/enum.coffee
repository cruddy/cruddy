class Cruddy.fields.Enum extends Field
    createEditableInput: (model) ->
        new SelectInput
            model: model
            key: @id
            prompt: @get "prompt"
            items: @get "items"

    createFilterInput: (model) ->
        new SelectInput
            model: model
            key: @id
            prompt: "Любое значение"
            items: @get "items"

    format: (value) ->
        items = @get "items"

        if value of items then items[value] else "n/a"