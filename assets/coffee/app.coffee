# Backend application file

class App extends Backbone.Model

    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @dfd = $.Deferred()

        @on "change:entity", @displayEntity, this

        this

    init: ->
        @loadSchema()

        return this

    ready: (callback) -> @dfd.done callback

    loadSchema: ->
        req = $.ajax
            url: entity_url "_schema"
            displayLoading: yes

        req.done (resp) =>
            @entities[entity.id] = new Cruddy.Entity.Entity entity for entity in resp.data

            @dfd.resolve this

            return

        req.fail =>
            @dfd.reject()

            @displayError Cruddy.lang.schema_failed

            return

        return req

    displayEntity: (model, entity) ->
        @dispose()

        @mainContent.hide()
        @container.append (@page = new Cruddy.Entity.Page model: entity).render().el if entity

    displayError: (error) ->
        @dispose()
        @mainContent.html("<p class='alert alert-danger'>#{ error }</p>").show()

        this

    startLoading: ->
        @loading = setTimeout (=>
            $(document.body).addClass "loading"
            @loading = no

        ), 1000 if @loadingRequests++ is 0

        this

    doneLoading: ->
        if @loadingRequests is 0
            console.error "Seems like doneLoading is called too many times."

            return

        if --@loadingRequests is 0
            if @loading
                clearTimeout @loading
                @loading = no
            else
                $(document.body).removeClass "loading"

        this

    entity: (id) ->
        console.error "Unknown entity #{ id }" if not id of @entities

        @entities[id]

    dispose: ->
        @page?.remove()

        this

class Router extends Backbone.Router

    initialize: ->
        @query = $.query

        entities = Cruddy.entities

        @addRoute "index", entities
        #@addRoute "update", entities, "([^/]+)"
        #@addRoute "create", entities, "create"

        root = Cruddy.root + "/" + Cruddy.uri + "/"
        history = Backbone.history

        $(document.body).on "click", "a", (e) =>
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

    getQuery: (key) -> @query.GET key

    setQuery: (key, value) -> @updateQuery @query.set key, value

    refreshQuery: (defaults, actual) ->
        q = @query.copy()

        for key, val of defaults
            if (value = actual[key]) isnt val
                q.SET key, value
            else
                q.REMOVE key

        @updateQuery q

    removeQuery: (key) -> @updateQuery @query.remove key

    updateQuery: (query) ->
        if (qs = query.toString()) isnt @query.toString()
            @query = query

            path = location.pathname
            uri = "/" + Cruddy.uri + "/"
            path = path.slice uri.length if path.indexOf(uri) is 0

            Backbone.history.navigate path + qs

        return this

    createApp: ->
        if not Cruddy.app
            Cruddy.app = new App
            Cruddy.app.init()

        return Cruddy.app

    addRoute: (name, entities, appendage = null) ->
        route = "^(#{ entities })"
        route += "/" + appendage if appendage
        route += "(\\?.*)?$"

        @route new RegExp(route), name

        this

    resolveEntity: (id, callback) -> @createApp().ready (app) ->
        entity = app.entity(id)

        if entity.viewPermitted()
            entity.set "instance", null
            Cruddy.app.set "entity", entity

            callback.call this, entity if callback
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

        return

    index: (entity) -> @resolveEntity entity

    #create: (entity) -> @resolveEntity entity, (entity) -> entity.actionCreate()

    #update: (entity, id) -> @resolveEntity entity, (entity) -> entity.actionUpdate id

$ ->
    Cruddy.router = new Router

    Backbone.history.start
        root: Cruddy.baseUrl + "/" + Cruddy.uri
        pushState: true
        hashChange: false