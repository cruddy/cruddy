# View that displays a form for an entity instance
class Cruddy.Entity.Form extends Cruddy.Layout.Layout
    className: "entity-form"

    events:
        "click [data-action]": "executeAction"

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

        @$footer.prepend @alert.render().el

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

    saveModel: ->
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

    destroyModel: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm(Cruddy.lang.confirm_delete) else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    copyModel: ->
        Cruddy.app.entityView.displayForm @model.copy()

        this

    refreshModel: ->
        return if @request?

        @setupRequest @model.fetch() if @confirmClose()

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

            @$progressBar.width(width + '%').parent().show()

            @$progressBar.parent().hide() if width is 100

        this

    closeForm: ->
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

    render: ->
        @$el.html @template()

        @$container = @$component "body"

        @$nav = @$component "nav"
        @$footer = @$component "footer"
        @$btnSave = @$component "save"
        @$deletedMsg = @$component "deleted-message"
        @$progressBar = @$component "progress"

        @$serviceMenu = @$component "service-menu"
        @$serviceMenuItems = @$component "service-menu-items"

        @update()

        super

    renderElement: (el) ->
        @$nav.append el.getHeader().render().$el

        super

    update: ->
        permit = @model.entity.getPermissions()
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        @$el.toggleClass "loading", @request?
        @$el.toggleClass "destroyed", isDeleted

        @$btnSave.text if isNew then Cruddy.lang.create else Cruddy.lang.save
        @$btnSave.attr "disabled", @request?
        @$btnSave.toggle not isDeleted and if isNew then permit.create else permit.update

        @$serviceMenu.toggle not isNew
        @$serviceMenuItems.html @renderServiceMenuItems() unless isNew

        this

    template: -> """
        <div class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container-fluid">
                <ul id="#{ @componentId "nav" }" class="nav navbar-nav"></ul>

                <ul id="#{ @componentId "service_menu" }" class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <span class="glyphicon glyphicon-th"></span> <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu" id="#{ @componentId "service-menu-items" }"></ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="#{ @componentId "body" }"></div>

        <footer id="#{ @componentId "footer" }">
            <span class="fs-deleted-message">#{ Cruddy.lang.model_deleted }</span>

            <button data-action="closeForm" id="#{ @componentId "close" }" type="button" class="btn btn-default">#{ Cruddy.lang.close }</button>
            <button data-action="saveModel" id="#{ @componentId "save" }" type="button" class="btn btn-primary"></button>

            <div class="progress">
                <div id="#{ @componentId "progress" }" class="progress-bar form-save-progress"></div>
            </div>
        </footer>
    """

    renderServiceMenuItems: ->
        entity = (model = @model).entity

        html = ""

        unless (isDeleted = model.isDeleted) or _.isEmpty items = model.meta.presentationActions
            html += render_presentation_actions items
            html += render_divider()

        html += """
            <li class="#{ class_if isDeleted, "disabled" }">
                <a data-action="refreshModel" href="#">
                    #{ Cruddy.lang.model_refresh }
                </a>
            </li>
        """

        html += """
            <li class="#{ class_if not entity.createPermitted(), "disabled" }">
                <a data-action="copyModel" href="#">
                    #{ Cruddy.lang.model_copy }
                </a>
            </li>
        """

        html += """
            <li class="divider"></li>

            <li class="#{ class_if isDeleted or not entity.deletePermitted(), "disabled" }">
                <a data-action="destroyModel" href="#">
                    <span class="glyphicon glyphicon-trash"></span> #{ Cruddy.lang.model_delete }
                </a>
            </li>
        """

        return html

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

    executeAction: (e) ->
        return if e.isDefaultPrevented()

        if (action = ($el = $ e.currentTarget).data "action") and action of this
            e.preventDefault()

            this[action].call this, $el

        return

Cruddy.Entity.Form.display = (instance) ->
    form = new Cruddy.Entity.Form model: instance

    $(document.body).append form.render().$el

    after_break => form.show()

    return form