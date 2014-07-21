class Cruddy.Layout.Container extends Cruddy.Layout.Element

    defaultMethod: null

    initialize: (options) ->
        super

        @$container = @$el
        @items = []

        @createItems options.items if options.items

        return this

    create: (options) ->
        method = options.method or @defaultMethod

        if not method or not _.isFunction this[method]
            console.error "Couldn't resolve method ", method 

            return

        return this[method].call this, options

    createItems: (items) ->
        @create item for item in items

        this

    append: (element) ->
        @items.push element

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

    focus: ->
        el.focus() if el = _.first @items

        return this