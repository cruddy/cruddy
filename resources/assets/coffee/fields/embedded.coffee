class Cruddy.Fields.Embedded extends Cruddy.Fields.BaseRelation

    viewConstructor: Cruddy.Fields.EmbeddedView

    parse: (model, items) ->
        return items if items instanceof Cruddy.Fields.RelatedCollection

        unless @attributes.multiple
            items = if items or @isRequired(model) then [ items ] else []

        ref = @getReference()
        items = (ref.createInstance item for item in items)

        if collection = model.get @id
            collection.reset items

            return collection

        return new Cruddy.Fields.RelatedCollection items,
            owner: model
            field: this
            maxItems: if @isMultiple() then null else 1

    hasChangedSinceSync: (model) -> model.get(@id).hasChangedSinceSync()

    copy: (copy, items) -> items.copy(copy)

    isMultiple: -> @attributes.multiple

    copyAttribute: (model, copy) -> model.get(@id).copy(copy)

    prepareAttribute: (value) -> if value then value.serialize() else value

    isCopyable: -> yes

    getType: -> "inline-relation"