class Cruddy.Fields.Markdown extends Field
    createEditableInput: (model) ->
        new Cruddy.Inputs.Markdown
            model: model
            key: @id
            height: @get "height"
            theme: @get "theme"