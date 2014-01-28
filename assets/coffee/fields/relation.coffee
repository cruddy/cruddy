class Cruddy.Fields.Relation extends Field
    createEditableInput: (model) ->
        new Cruddy.Inputs.EntityDropdown
            model: model
            key: @id
            multiple: @get "multiple"
            reference: @get "reference"

    createFilterInput: (model) ->
        new Cruddy.Inputs.EntityDropdown
            model: model
            key: @id
            reference: @get "reference"
            allowEdit: no

    format: (value) ->
        return "не указано" if _.isEmpty value
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title