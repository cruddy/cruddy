class Cruddy.Fields.BaseDateTime extends Cruddy.Fields.Base

    inputFormat: null
    mask: null

    createEditableInput: (model, inputId) -> new Cruddy.Inputs.DateTime
        model: model
        key: @id
        format: @inputFormat
        mask: @mask
        attributes:
            id: @inputId
    
    format: (value) -> if value is null then NOT_AVAILABLE else moment.unix(value).format(@inputFormat)

class Cruddy.Fields.Date extends Cruddy.Fields.BaseDateTime
    inputFormat: "YYYY-MM-DD"
    mask: "9999-99-99"

class Cruddy.Fields.Time extends Cruddy.Fields.BaseDateTime
    inputFormat: "HH:mm:ss"
    mask: "99:99:99"

class Cruddy.Fields.DateTime extends Cruddy.Fields.BaseDateTime
    inputFormat: "YYYY-MM-DD HH:mm:ss"
    mask: "9999-99-99 99:99:99"
    
    # format: (value) -> if value is null then NOT_AVAILABLE else moment.unix(value).calendar()