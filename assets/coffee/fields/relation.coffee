class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createEditableInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReference()

    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: @getReference()
        allowEdit: no

    format: (value) ->
        return "не указано" if _.isEmpty value
        
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title

    isEditable: -> super and @getReference().viewPermitted()

    canFilter: -> super and @getReference().viewPermitted()