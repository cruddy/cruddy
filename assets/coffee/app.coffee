# Backend application file

$(".navbar").on "click", ".entity", (e) =>
    e.preventDefault();

    baseUrl = Cruddy.root + "/" + Cruddy.uri + "/"
    href = e.currentTarget.href.substr baseUrl.length

    Cruddy.router.navigate href, trigger: true

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

    routes: {
        ":page": "page"
        ":page/create": "create"
        ":page/:id": "update"
    }

    entity: (id) ->
        entity = Cruddy.app.entity(id)

        if not entity
            Cruddy.app.displayError Cruddy.lang.entity_not_found

            return

        if entity.viewPermitted()
            entity.set "instance", null
            Cruddy.app.set "entity", entity

            entity
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

            null

    page: (page) -> @entity page

    create: (page) ->
        entity = @entity page
        entity.actionCreate() if entity

        entity

    update: (page, id) ->
        entity = @entity page

        entity.actionUpdate id if entity

        entity

Cruddy.router = new Router

Backbone.history.start { root: Cruddy.uri + "/", pushState: true, hashChange: false }