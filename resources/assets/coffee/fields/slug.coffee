class Cruddy.Fields.Slug extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Slug
        model: model
        key: @id
        field: @attributes.field

        attributes:
            placeholder: @attributes.placeholder

    getType: -> "slug"