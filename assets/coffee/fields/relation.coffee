class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createEditableInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReference()
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint

    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: @getReference()
        allowEdit: no
        placeholder: Cruddy.lang.any_value
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint

    isEditable: -> super and @getReference().viewPermitted()

    canFilter: -> super and @getReference().viewPermitted()