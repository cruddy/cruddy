class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createInput: (model, inputId, forceDisable = no) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReferencedEntity()
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint
        enabled: not forceDisable and @isEditable(model)

    isEditable: -> @getReferencedEntity().readPermitted() and super

    canFilter: -> @getReferencedEntity().readPermitted() and super

    formatItem: (item) ->
        ref = @getReferencedEntity()

        return item.body unless ref.readPermitted()

        """<a href="#{ ref.link item.id }">#{ item.body }</a>"""

    prepareAttribute: (value) ->
        return null unless value?

        return _.pluck(value, "id").join(",") if _.isArray value

        return value.id