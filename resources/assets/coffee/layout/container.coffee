class Cruddy.Layout.Container extends Cruddy.Layout.Element

    initialize: (options) ->
        super

        @$container = @$el
        @items = []

        @createItems options.items if options.items

        return this

    create: (options) ->
        constructor = get options.class

        if not constructor or not _.isFunction constructor
            console.error "Couldn't resolve element of type ", options.class

            return

        @append new constructor options, this

    createItems: (items) ->
        @create item for item in items

        this

    append: (element) ->
        @items.push element if element

        return element

    renderElement: (element) ->
        @$container.append element.render().$el

        return this

    render: ->
        @renderElement element for element in @items if @items

        super

    remove: ->
        item.remove() for item in @items

        super

    getFocusable: -> _.find @items, (item) -> item.isFocusable()

    isFocusable: -> return @getFocusable()?

    focus: ->
        el.focus() if el = @getFocusable()

        return this