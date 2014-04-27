# Backend application file

class App extends Backbone.Model
    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @entitiesDfd = {}

        # Create entities
        @entities[entity.id] = new Cruddy.Entity.Entity entity for entity in Cruddy.entities

        @on "change:entity", @displayEntity, this

        this

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

Cruddy.app = new App

class Router extends Backbone.Router

    initialize: ->
        entities = (_.map Cruddy.entities, (entity) -> entity.id).join "|"

        @addRoute "index", entities
        @addRoute "update", entities, "([^/]+)"
        @addRoute "create", entities, "create"

        root = Cruddy.root + "/" + Cruddy.uri + "/"
        history = Backbone.history
        hashStripper = /#.*$/

        $(document.body).on "click", "a", (e) ->
            fragment = e.currentTarget.href.replace hashStripper, ""
            oldFragment = history.fragment

            if fragment.indexOf(root) is 0 and (fragment = fragment.slice root.length) and fragment isnt oldFragment
                loaded = history.loadUrl fragment

                # Backbone will set fragment even if no route matched so we need to
                # restore old fragment
                history.fragment = oldFragment

                if loaded
                    e.preventDefault()
                    history.navigate fragment

            e

        this

    addRoute: (name, entities, appendage = null) ->
        route = "^(#{ entities })"
        route += "/" + appendage if appendage
        route += "$"

        @route new RegExp(route), name

        this

    resolveEntity: (id) ->
        entity = Cruddy.app.entity(id)

        if entity.viewPermitted()
            entity.set "instance", null
            Cruddy.app.set "entity", entity

            entity
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

            null

    index: (entity) -> @resolveEntity entity

    create: (entity) ->
        console.log 'create'

        entity = @resolveEntity entity
        entity.actionCreate() if entity

        entity

    update: (entity, id) ->
        entity = @resolveEntity entity

        entity.actionUpdate id if entity

        entity

Cruddy.router = new Router

Backbone.history.start
    root: Cruddy.uri
    pushState: true
    hashChange: false