
class Cruddy.Fields.DateTime extends Cruddy.Fields.Base

    createEditableInput: (model, inputId) -> new Cruddy.Inputs.DateTime
        model: model
        key: @id
        format: @attributes.format
        attributes:
            id: @inputId
    
    format: (value) -> if value is null then Cruddy.lang.never else moment.unix(value).calendar()