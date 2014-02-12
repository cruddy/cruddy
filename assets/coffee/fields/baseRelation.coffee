class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReference().viewPermitted() and super

    # Get the referenced entity
    getReference: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReference().getSingularTitle()