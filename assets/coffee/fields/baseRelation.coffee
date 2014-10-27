class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReference().viewPermitted() and super

    # Get the referenced entity
    getReference: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReference().getSingularTitle()

    linkTo: (item) ->
        ref = @getReference()

        return item.title unless ref.viewPermitted()

        """<a href="#{ ref.link item.id }">#{ _.escape item.title }</a>"""

    format: (value) ->
        return NOT_AVAILABLE if _.isEmpty value

        if @attributes.multiple then _.map(value, (item) => @linkTo item).join ", " else @linkTo value