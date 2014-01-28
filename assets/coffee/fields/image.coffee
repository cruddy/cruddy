class Cruddy.Fields.Image extends Cruddy.Fields.File
    createEditableInput: (model) -> new Cruddy.Inputs.ImageList
        model: model
        key: @id
        width: @get "width"
        height: @get "height"
        multiple: @get "multiple"
        accepts: @get "accepts"

    format: (value) -> if value instanceof File then value.name else value