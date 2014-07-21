class Cruddy.Layout.Layout extends Cruddy.Layout.Container

    initialize: ->
        super

        @setupLayout()

    setupLayout: ->
        if @entity.attributes.layout
            @createItems @entity.attributes.layout
        else
            @setupDefaultLayout()

        return this

    setupDefaultLayout: -> return this
    
    tab: (options) -> @append new Cruddy.Layout.TabPane options, this