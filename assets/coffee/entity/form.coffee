# View that displays a form for an entity instance
class Cruddy.Entity.Form extends Cruddy.Layout.Layout
    className: "entity-form"

    events:
        "click .btn-save": "save"
        "click .btn-close": "close"
        "click .btn-destroy": "destroy"
        "click .btn-copy": "copy"
        "click .fs-btn-refresh": "refresh"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: (options) ->
        super

        @listenTo @model, "destroy", @handleModelDestroyEvent
        @listenTo @model, "invalid", @handleModelInvalidEvent

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        return this

    setupDefaultLayout: ->
        tab = @append new Cruddy.Layout.TabPane { title: @model.entity.get("title").singular }, this

        tab.append new Cruddy.Layout.Field { field: field.id }, tab for field in @entity.fields.models

        return this

    hotkeys: (e) ->
        # Ctrl + Z
        if e.ctrlKey and e.keyCode is 90 and e.target is document.body
            @model.set @model.previousAttributes()
            return false

        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @save()
            return false

        # Escape
        if e.keyCode is 27
            @close()
            return false

        this

    displayAlert: (message, type, timeout) ->
        @alert.remove() if @alert?

        @alert = new Alert
            message: message
            className: "flash"
            type: type
            timeout: timeout

        @footer.prepend @alert.render().el

        this

    displaySuccess: -> @displayAlert Cruddy.lang.success, "success", 3000

    displayError: (xhr) -> @displayAlert Cruddy.lang.failure, "danger", 5000 unless xhr.status is 400

    handleModelInvalidEvent: -> @displayAlert Cruddy.lang.invalid, "warning", 5000

    handleModelDestroyEvent: ->
        @update()

        @trigger "destroyed", @model

        this

    show: ->
        @$el.toggleClass "opened", true

        @items[0].activate()

        @focus()

        this

    refresh: ->
        return if @request?

        @setupRequest @model.fetch() if @confirmClose()

        return this

    save: ->
        return if @request?

        isNew = @model.isNew()

        @setupRequest @model.save null,
            displayLoading: yes

            xhr: =>
                xhr = $.ajaxSettings.xhr()
                xhr.upload.addEventListener('progress', $.proxy @, "progressCallback") if xhr.upload

                xhr

        @request.done (resp) =>
            @trigger (if isNew then "created" else "updated"), @model, resp
            @trigger "saved", @model, resp

        return this

    setupRequest: (request) ->
        request.done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        request.always =>
            @request = null
            @update()

        @request = request

        @update()

    progressCallback: (e) ->
        if e.lengthComputable
            width = (e.loaded * 100) / e.total

            @progressBar.width(width + '%').parent().show()

            @progressBar.parent().hide() if width is 100

        this

    close: ->
        if @confirmClose()
            @remove()
            @trigger "close"

        this

    pageUnloadConfirmationMessage: ->
        return if @model.isDeleted

        return Cruddy.lang.onclose_abort if @request

        return Cruddy.lang.onclose_discard if @model.hasChangedSinceSync()

    confirmClose: ->
        unless @model.isDeleted
            return confirm Cruddy.lang.confirm_abort if @request
            return confirm Cruddy.lang.confirm_discard if @model.hasChangedSinceSync()

        return yes

    destroy: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm(Cruddy.lang.confirm_delete) else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    copy: ->
        Cruddy.app.page.displayForm @model.copy()

        this

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        @nav = @$component "nav"
        @footer = @$ "footer"
        @submit = @$ ".btn-save"
        @$deletedMsg = @$component "deleted-message"
        @destroy = @$ ".btn-destroy"
        @copy = @$ ".btn-copy"
        @$refresh = @$ ".fs-btn-refresh"
        @progressBar = @$ ".form-save-progress"

        @update()

        super

    renderElement: (el) ->
        @nav.append el.getHeader().render().$el

        super

    update: ->
        permit = @model.entity.getPermissions()
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        @$el.toggleClass "loading", @request?

        @submit.text if isNew then Cruddy.lang.create else Cruddy.lang.save
        @submit.attr "disabled", @request?
        @submit.toggle not isDeleted and if isNew then permit.create else permit.update

        @destroy.attr "disabled", @request?
        @destroy.toggle not isDeleted and not isNew and permit.delete

        @$deletedMsg.toggle isDeleted

        @copy.toggle not isNew and permit.create
        @$refresh.attr "disabled", @request?
        @$refresh.toggle not isNew and not isDeleted

        @external?.remove()

        @$refresh.after @external = $ @externalLinkTemplate @model.extra.external if @model.extra.external

        this

    template: ->
        """
        <div class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container-fluid">
                <ul id="#{ @componentId "nav" }" class="nav navbar-nav"></ul>
            </div>
        </div>

        <div class="tab-content" id="#{ @componentId "body" }"></div>

        <footer>
            <div class="pull-left">
                <button type="button" class="btn btn-link btn-destroy" title="#{ Cruddy.lang.model_delete }">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>

                <button type="button" tabindex="-1" class="btn btn-link btn-copy" title="#{ Cruddy.lang.model_copy }">
                    <span class="glyphicon glyphicon-book"></span>
                </button>

                <button type="button" class="btn btn-link fs-btn-refresh" title="#{ Cruddy.lang.model_refresh }">
                    <span class="glyphicon glyphicon-refresh"></span>
                </button>
            </div>

            <span class="fs-deleted-message" id="#{ @componentId "deleted-message" }">#{ Cruddy.lang.model_deleted }</span>
            <button type="button" class="btn btn-default btn-close">#{ Cruddy.lang.close }</button>
            <button type="button" class="btn btn-primary btn-save"></button>

            <div class="progress"><div class="progress-bar form-save-progress"></div></div>
        </footer>
        """

    externalLinkTemplate: (href) -> """
        <a href="#{ href }" class="btn btn-link" title="#{ Cruddy.lang.view_external }" target="_blank">
            #{ b_icon "eye-open" }
        </a>
        """

    remove: ->
        @trigger "remove", @

        @request.abort() if @request

        @$el.one(TRANSITIONEND, =>
            $(document).off "." + @cid

            @trigger "removed", @

            super
        )
        .removeClass "opened"

        super

Cruddy.Entity.Form.display = (instance) ->
    form = new Cruddy.Entity.Form model: instance

    $(document.body).append form.render().$el

    after_break => form.show()

    return form