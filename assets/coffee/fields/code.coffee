class Cruddy.Fields.Code extends Field
    createEditableInput: (model) ->
        new Cruddy.Inputs.Markdown
            model: model
            key: @id
            height: @get "height"
            mode: @get "mode"
            theme: @get "theme"