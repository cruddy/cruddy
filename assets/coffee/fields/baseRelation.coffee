class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReference().viewPermitted() and super

    # Get the referenced entity
    getReference: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReference().getSingularTitle()

    formatItem: (item) -> item.title

    format: (value) ->
        return NOT_AVAILABLE if _.isEmpty value

        if @attributes.multiple then _.map(value, (item) => @formatItem item).join ", " else @formatItem value