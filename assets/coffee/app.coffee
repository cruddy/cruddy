# Backend application file

class App extends Backbone.Model

    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @dfd = $.Deferred()

        @$error = $(@errorTemplate()).appendTo @container

        @$error.on "click", ".close", => @$error.stop(yes).fadeOut()

        @on "change:entity", @displayEntity, this

        $(document).ajaxError (event, xhr, xhrOptions) => @handleAjaxError xhr, xhrOptions

        this

    errorTemplate: -> """
        <p class="alert alert-danger cruddy-global-error">
            <button type="button" class="close">&times;</button>
            <span class="data"></span>
        </p>
    """

    init: ->
        @loadSchema()

        return this

    ready: (callback) -> @dfd.done callback

    loadSchema: ->
        req = $.ajax
            url: Cruddy.schemaUrl
            displayLoading: yes

        req.done (resp) =>
            @entities[entity.id] = new Cruddy.Entity.Entity entity for entity in resp

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

    handleAjaxError: (xhr) ->
        if xhr.responseJSON?.error
            @$error.children(".data").text(xhr.responseJSON.error).end().stop(yes).fadeIn()

        return

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