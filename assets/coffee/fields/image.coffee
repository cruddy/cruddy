class Cruddy.fields.Image extends Cruddy.fields.File
    createEditableInput: (model) -> new ImageList
        model: model
        key: @id
        width: @get "width"
        height: @get "height"
        multiple: @get "multiple"
        accepts: @get "accepts"

    format: (value) -> if value instanceof File then value.name else value