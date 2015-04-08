class Cruddy.Fields.Slug extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Slug
        model: model
        key: @id
        chars: @attributes.chars
        field: @attributes.field
        separator: @attributes.separator

        attributes:
            placeholder: @attributes.placeholder

    getType: -> "slug"