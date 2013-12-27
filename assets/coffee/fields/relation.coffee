class Cruddy.fields.Relation extends Field
    createEditableInput: (model) ->
        new EntityDropdown
            model: model
            key: @id
            multiple: @get "multiple"
            reference: @get "reference"

    createFilterInput: (model) -> @createEditableInput model

    format: (value) ->
        return "не указано" if _.isEmpty value
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title

Cruddy.fields.register "Relation", Cruddy.fields.Relation