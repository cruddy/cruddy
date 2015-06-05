class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createInput: (model, inputId, forceDisable = no) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReferencedEntity()
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint
        enabled: not forceDisable and @isEditable(model)

    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: @getReferencedEntity()
        allowEdit: no
        placeholder: Cruddy.lang.any_value
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint
        multiple: yes

    isEditable: -> @getReferencedEntity().readPermitted() and super

    canFilter: -> @getReferencedEntity().readPermitted() and super

    formatItem: (item) ->
        ref = @getReferencedEntity()

        return item.title unless ref.readPermitted()

        """<a href="#{ ref.link item.id }">#{ _.escape item.title }</a>"""

    prepareAttribute: (value) ->
        return null unless value?

        return _.pluck(value, "id").join(",") if _.isArray value

        return value.id

    prepareFilterData: (value) ->
        value = super

        if _.isEmpty value then null else value

    parseFilterData: (value) ->
        return null unless _.isString(value) or _.isNumber(value)

        value = value.toString()

        return null unless value.length

        value = value.split ","

        return _.map value, (value) -> { id: value }