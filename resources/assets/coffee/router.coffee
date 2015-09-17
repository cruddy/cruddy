# Cruddy router

class Router extends Backbone.Router

    initialize: ->
        @query = $.query

        entities = Cruddy.entities

        @addRoute "index", entities

        root = Cruddy.baseUrl
        history = Backbone.history

        $(document).on "click", "a", (e) =>
            return if e.isDefaultPrevented()

            fragment = e.currentTarget.href

            return if fragment.indexOf(root) isnt 0

            fragment = history.getFragment fragment.slice root.length

            # Try to find a handler for the fragment and if it is found, navigate
            # to it and cancel the default event
            for handler in history.handlers when handler.route.test(fragment)
                e.preventDefault()
                history.navigate fragment, trigger: yes

                break

            return

        this

    execute: ->
        @query = $.query.parseNew location.search

        super

    navigate: (fragment) ->
        @query = @query.load fragment

        super

    # Get the query parameter value
    getQuery: (key) -> @query.GET key

    # Set the query parameter value
    setQuery: (key, value, options) -> @updateQuery @query.set(key, value), options

    refreshQuery: (params, defaults = {}, options) ->
        query = @query.copy()

        for key, value of params
            if value is null or (key of defaults and value == defaults[key])
                query.REMOVE key
            else
                query.SET key, value

        @updateQuery query, options

    # Remove the key from the query
    removeQuery: (key, options) -> @updateQuery @query.remove(key), options

    updateQuery: (query, options) ->
        if (qs = query.toString()) isnt @query.toString()
            @query = query

            path = location.pathname
            uri = "/" + Cruddy.uri + "/"
            path = path.slice uri.length if path.indexOf(uri) is 0

            Backbone.history.navigate path + qs, options

        return this

    addRoute: (name, entities, appendage = null) ->
        route = "^(#{ entities })"
        route += "/" + appendage if appendage
        route += "(\\?.*)?$"

        @route new RegExp(route), name

        this

    resolveEntity: (id, callback) -> Cruddy.ready (app) ->
        entity = app.entity(id)

        if entity.readPermitted()
            Cruddy.app.set "entity", entity

            callback.call this, entity if callback
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

        return

    index: (entity) -> @resolveEntity entity

$ ->
    Cruddy.router = new Router

    Backbone.history.start
        root: Cruddy.getHistoryRoot()
        pushState: true
        hashChange: false