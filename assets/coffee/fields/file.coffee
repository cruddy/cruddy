class Cruddy.fields.File extends Field
    createEditableInput: (model) -> new FileList
        model: model
        key: @id
        multiple: @get "multiple"
        accepts: @get "accepts"

    format: (value) -> if value instanceof File then value.name else value