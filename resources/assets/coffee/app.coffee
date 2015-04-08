# Backend application file

class App extends Backbone.Model

    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @dfd = $.Deferred()

        @$title = $ "title"

        @$error = $(@errorTemplate()).appendTo @container

        @$error.on "click", ".close", => @$error.stop(yes).fadeOut()

        @on "change:entity", @displayEntity, this

        $(document).ajaxError (event, xhr, xhrOptions) => @handleAjaxError xhr, xhrOptions
        $(window).on "beforeunload", => @pageUnloadConfirmationMessage()

        this

    errorTemplate: -> """
        <p class="alert alert-danger cruddy-global-error">
            <button type="button" class="close">&times;</button>
            <span class="data"></span>
        </p>
    """

    init: ->
        @_loadSchema()

        return this

    ready: (callback) -> @dfd.done callback

    _loadSchema: ->
        req = $.ajax
            url: Cruddy.schemaUrl
            displayLoading: yes

        req.done (resp) =>
            for entity in resp
                modelClass = get(entity.model_class) or Cruddy.Entity.Entity

                @entities[entity.id] = new modelClass entity

            @dfd.resolve @

            $(document).trigger "started.cruddy", @

            return

        req.fail =>
            @dfd.reject()

            @displayError Cruddy.lang.schema_failed

            return

        return req

    displayEntity: (model, entity) ->
        @dispose()

        @mainContent.hide()

        @container.append (@entityView = entity.createController()).render().el if entity

        @updateTitle()

    displayError: (error) ->
        @dispose()
        @mainContent.html("<p class='alert alert-danger'>#{ error }</p>").show()

        this

    handleAjaxError: (xhr) ->
        return if xhr.status is VALIDATION_FAILED_CODE

        if xhr.responseJSON?.error
            if _.isObject error = xhr.responseJSON.error
                error = error.type + ": " + error.message
        else
            error = "Unknown error occurred"

        @$error.children(".data").text(error).end().stop(yes).fadeIn()

        return

    pageUnloadConfirmationMessage: -> return @entityView?.pageUnloadConfirmationMessage()

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
        throw "Unknown entity #{ id }" unless id of @entities

        @entities[id]

    dispose: ->
        @entityView?.remove()

        @entityView = null

        this

    updateTitle: ->
        title = Cruddy.brandName

        title = @entityView.getPageTitle() + TITLE_SEPARATOR + title if @entityView?

        @$title.text title

        return this
