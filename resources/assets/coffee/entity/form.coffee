# View that displays a form for an entity instance
class Cruddy.Entity.Form extends Cruddy.View
    className: "entity-form"

    events:
        "click [data-action]": "executeAction"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: (options) ->
        @formType = null

        @saveOptions =
            displayLoading: yes

            xhr: =>
                xhr = $.ajaxSettings.xhr()
                xhr.upload.addEventListener('progress', $.proxy @, "progressCallback") if xhr.upload

                xhr

        @listenTo @model, "destroy", @handleModelDestroyEvent
        @listenTo @model, "invalid", @handleModelInvalidEvent

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        super

    hotkeys: (e) ->
        # Ctrl + Z
        if e.ctrlKey and e.keyCode is 90 and e.target is document.body
            @model.set @model.previousAttributes()
            return false

        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @saveModel()
            return false

        # Escape
        if e.keyCode is 27
            @closeForm()
            e.preventDefault()

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

    displaySuccess: (resp) ->
        @displayAlert Cruddy.lang.form_saved, "success", 3000

        Cruddy.getApp().displayActionResult resp.actionResult if resp.actionResult

        return this

    displayError: (xhr) ->
        unless xhr.status is VALIDATION_FAILED_CODE
            @displayAlert Cruddy.lang.form_failed, "danger", 5000

        return this

    handleModelInvalidEvent: -> @displayAlert Cruddy.lang.form_invalid, "warning", 5000

    handleModelDestroyEvent: ->
        @updateModelState()

        @trigger "destroyed", @model

        this

    show: ->
        @$el.toggleClass "opened", true

        @layout.activate()

        this

    save: (options) ->
        return if @request?

        isNew = @model.isNew()

        @setupRequest @model.save null, $.extend {}, @saveOptions, options

        @request.done (resp) =>
            @trigger (if isNew then "created" else "updated"), @model, resp
            @trigger "saved", @model, resp

            @updateModelState()

        return this

    saveModel: -> @save()

    saveWithAction: ($el) -> @save url: @model.entity.url @model.id + "/" + $el.data "actionId"

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

        @request.done => @updateModelMetaState()

        return this

    setupRequest: (request) ->
        request.done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        request.always =>
            @request = null
            @updateRequestState()

        @request = request

        @updateRequestState()

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

        @$nav = @$component "nav"
        @$navbar = @$component "navbar"
        @$footer = @$component "footer"
        @$btnSave = @$component "save"
        @$deletedMsg = @$component "deleted-message"
        @$progressBar = @$component "progress"

        @$serviceMenu = @$component "service-menu"
        @$serviceMenuItems = @$component "service-menu-items"

        @updateModelState()

        super

    updateRequestState: ->
        isLoading = @request?

        @$el.toggleClass "loading", isLoading
        @$btnSave.attr "disabled", isLoading

        if @$btnExtraActions
            @$btnExtraActions.attr "disabled", isLoading
            @$btnExtraActions.children(".btn").attr "disabled", isLoading

        this

    updateModelState: ->
        entity = @model.entity
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        formType = if isNew then "create" else "update"

        if formType != @formType
            @layout?.remove()

            @formType = formType
            form = entity.form formType

            @layout = @createLayout form

            @$navbar.after @layout.render().$el

            @$nav.empty()

            @layout.items.forEach (item) => @$nav.append item.getHeader().render().$el

            after_break => @layout.activate()

        @$el.toggleClass "destroyed", isDeleted

        @$btnSave.text if isNew then Cruddy.lang.create else Cruddy.lang.save
        @$btnSave.toggle not isDeleted and if isNew then entity.createPermitted() else entity.updatePermitted()

        @updateModelMetaState()

    createLayout: (form) ->
        if form.layout
            return new Cruddy.Layout.Layout
                form: form
                model: @model
                items: form.layout
                el: @$container

        layout = new Cruddy.Layout.Layout
            form: form
            model: @model
            el: @$container

        tab = layout.append new Cruddy.Layout.TabPane
            title: @model.entity.getSingularTitle()
            parent: layout
            model: @model

        form.fields.forEach (field) ->
            tab.append new Cruddy.Layout.Field
                field: field.id
                parent: tab
                model: @model

        return layout

    updateModelMetaState: ->
        isNew = @model.isNew()
        isDeleted = @model.isDeleted or false

        @$serviceMenu.toggle not isNew
        @$serviceMenuItems.html @renderServiceMenuItems() unless isNew

        @$btnExtraActions?.remove()
        @$btnExtraActions = null

        if @model.entity.updatePermitted()
            @$btnSave.before @$btnExtraActions = $ html if not isNew and not isDeleted and html = @renderExtraActionsButton()

        return this

    template: -> """
        <div class="navbar navbar-default navbar-static-top" role="navigation" id="#{ @componentId "navbar" }">
            <div class="container-fluid">
                <ul id="#{ @componentId "nav" }" class="nav navbar-nav"></ul>

                <ul id="#{ @componentId "service-menu" }" class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <span class="glyphicon glyphicon-option-horizontal"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu" id="#{ @componentId "service-menu-items" }"></ul>
                    </li>
                </ul>
            </div>
        </div>

        <footer id="#{ @componentId "footer" }">
            <span class="fs-deleted-message">#{ Cruddy.lang.model_deleted }</span>

            <button data-action="closeForm" id="#{ @componentId "close" }" type="button" class="btn btn-default">#{ Cruddy.lang.close }</button><!--
            --><button data-action="saveModel" id="#{ @componentId "save" }" type="button" class="btn btn-primary btn-save"></button>

            <div class="progress">
                <div id="#{ @componentId "progress" }" class="progress-bar form-save-progress"></div>
            </div>
        </footer>
    """

    renderServiceMenuItems: ->
        entity = (model = @model).entity

        html = ""

        unless (isDeleted = model.isDeleted) or _.isEmpty items = model.meta.links
            html += render_presentation_actions items
            html += render_divider()

        html += """
            <li class="#{ value_if isDeleted, "disabled" }">
                <a data-action="refreshModel" href="#">
                    #{ Cruddy.lang.model_refresh }
                </a>
            </li>
        """

        html += """
            <li class="#{ value_if not entity.createPermitted(), "disabled" }">
                <a data-action="copyModel" href="#">
                    #{ Cruddy.lang.model_copy }
                </a>
            </li>
        """

        html += """
            <li class="divider"></li>

            <li class="#{ value_if isDeleted or not entity.deletePermitted(), "disabled" }">
                <a data-action="destroyModel" href="#">
                    <span class="glyphicon glyphicon-trash"></span> #{ Cruddy.lang.model_delete }
                </a>
            </li>
        """

        return html

    renderExtraActionsButton: ->
        return if _.isEmpty @model.meta.actions

        mainAction = _.find(@model.meta.actions, (item) -> not item.disabled) or _.first(@model.meta.actions)

        button = """
            <button data-action="saveWithAction" data-action-id="#{ mainAction.id }" type="button" class="btn btn-#{ mainAction.state }" #{ value_if mainAction.isDisabled, "disabled" }>
                #{ mainAction.title }
            </button>
        """

        return @wrapWithExtraActions(button, mainAction)

    wrapWithExtraActions: (button, mainAction) ->
        actions = _.filter @model.meta.actions, (action) -> action isnt mainAction

        return button if _.isEmpty actions

        html = ""
        html += """
            <li class="#{ value_if action.disabled, "disabled" }">
                <a data-action="saveWithAction" data-action-id="#{ action.id }" href="#">#{ action.title }</a>
            </li>
        """ for action in actions

        return """
            <div class="btn-group dropup">
                #{ button }

                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>

                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    #{ html }
                </ul>
            </div>
        """

    remove: ->
        @trigger "remove", @

        @request.abort() if @request
        $(document).off "." + @cid

        @$el.one TRANSITIONEND, =>
            @trigger "removed", @

            super

        @$el.removeClass "opened"

        super

    executeAction: (e) ->
        return if e.isDefaultPrevented()

        if (action = ($el = $ e.currentTarget).data "action") and action of this
            e.preventDefault()

            this[action].call this, $el

        return

    # Get a field with specified id
    field: (id) ->
        if not field = @fields.get id
            console.error "The field #{id} is not found."

            return

        return field

Cruddy.Entity.Form.display = (instance) ->
    form = new Cruddy.Entity.Form model: instance

    $(document.body).append form.render().$el

    after_break => form.show()

    return form