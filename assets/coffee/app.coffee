# Backend application file

$(".navbar").on "click", ".entity", (e) =>
    e.preventDefault();

    baseUrl = Cruddy.root + "/" + Cruddy.uri + "/"
    href = e.currentTarget.href.substr baseUrl.length

    Cruddy.router.navigate href, trigger: true

class App extends Backbone.Model
    entities: {}

    initialize: ->
        @container = $ "#container"
        @loadingRequests = 0

        @on "change:entity", @displayEntity, this

    displayEntity: (model, entity) ->
        @dispose()

        @container.html (@page = new EntityPage model: entity).render().el if entity

    displayError: (xhr) ->
        error = if not xhr? or xhr.status is 403 then "Ошибка доступа" else "Ошибка"

        @dispose()
        @container.html "<p class='alert alert-danger'>#{ error }</p>"

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

    entity: (id, options = {}) ->
        return @entities[id] if id of @entities

        options = $.extend {}, {
            url: entity_url id, "schema"
            type: "get"
            dataType: "json"
            displayLoading: yes

        }, options

        @entities[id] = $.ajax(options).then (resp) =>
            entity = new Entity resp.data

            return entity if _.isEmpty entity.related.models

            # Resolve all related entites
            wait = (related.resolve() for related in entity.related.models)

            $.when.apply($, wait).then -> entity

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

    loading: (promise) ->
        Cruddy.app.startLoading()
        promise.always -> Cruddy.app.doneLoading()

    entity: (id) ->
        promise = Cruddy.app.entity(id).done (entity) ->
            entity.set "instance", null
            Cruddy.app.set "entity", entity

        promise.fail -> Cruddy.app.displayError.apply(Cruddy.app, arguments).set "entity", false

    page: (page) -> @entity page

    create: (page) -> @entity(page).done (entity) -> entity.set "instance", entity.createInstance()

    update: (page, id) -> @entity(page).then (entity) -> entity.update(id)

Cruddy.router = new Router

Backbone.history.start { root: Cruddy.uri + "/", pushState: true, hashChange: false }