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

        @on "change:entity", @displayEntity, this

    displayEntity: (model, entity) ->
        @page.remove() if @page?
        @container.append (@page = new EntityPage model: entity).render().el if entity?

    entity: (id) ->
        if id of @entities
            promise = $.Deferred().resolve(@entities[id]).promise()
        else
            promise = @fields(id).then (resp) =>
                @entities[id] = entity = new Entity resp.data

                return entity if _.isEmpty entity.related

                # Resolve all related entites
                wait = (related.resolve() for key, related of entity.related)

                $.when.apply($, wait).then -> entity

        promise

    fields: (id) -> $.getJSON "#{ API_URL }/#{ id }/entity"

Cruddy.app = new App

class Router extends Backbone.Router

    routes: {
        ":page": "page"
        ":page/create": "create"
        ":page/:id": "update"
    }

    page: (page) -> Cruddy.app.entity(page).then (entity) ->
        entity.set "instance", null
        Cruddy.app.set "entity", entity
        entity

    create: (page) -> @page(page).then (entity) ->
        entity.set "instance", entity.createInstance()

    update: (page, id) -> @page(page).then (entity) -> entity.update(id)

Cruddy.router = new Router

Backbone.history.start { root: Cruddy.uri + "/", pushState: true, hashChange: false }