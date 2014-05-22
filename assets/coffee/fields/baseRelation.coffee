class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReference().viewPermitted() and super

    # Get the referenced entity
    getReference: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReference().getSingularTitle()

    format: (value) ->
        return NOT_AVAILABLE if _.isEmpty value
        
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title