class Cruddy.Fields.Markdown extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Markdown
        model: model
        key: @id
        height: @attributes.height
        theme: @attributes.theme

    format: (value) -> if value then "<div class=\"well limit-height\">#{ marked value }</div>" else "n/a"