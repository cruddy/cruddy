class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReferencedEntity().readPermitted() and super

    # Get the referenced entity
    getReferencedEntity: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReferencedEntity().getSingularTitle()

    formatItem: (item) -> item.title

    format: (value) ->
        return NOT_AVAILABLE if _.isEmpty value

        if @attributes.multiple then _.map(value, (item) => @formatItem item).join ", " else @formatItem value

    getType: -> "relation"