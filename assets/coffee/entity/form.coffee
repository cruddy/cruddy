# View that displays a form for an entity instance
class Cruddy.Entity.Form extends Backbone.View
    className: "entity-form"

    events:
        "click .btn-save": "save"
        "click .btn-close": "close"
        "click .btn-destroy": "destroy"
        "click .btn-copy": "copy"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: (options) ->
        @inner = options.inner ? no

        @listenTo @model, "destroy", @handleDestroy
        @listenTo @model, "invalid", @displayInvalid

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        this

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

    displayAlert: (message, type) ->
        @alert.remove() if @alert?

        @alert = new Alert
            message: message
            className: "flash"
            type: type
            timeout: 3000

        @footer.prepend @alert.render().el

        this

    displaySuccess: -> @displayAlert Cruddy.lang.success, "success"

    displayInvalid: -> @displayAlert Cruddy.lang.invalid, "warning"

    displayError: (xhr) -> @displayAlert Cruddy.lang.failure, "danger" unless xhr.responseJSON?.error is "VALIDATION"

    handleDestroy: ->
        if @model.entity.get "soft_deleting"
            @update()
        else
            if @inner then @remove() else Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    show: ->
        @$el.toggleClass "opened", true
        @tabs[0].focus()

        this

    save: ->
        return if @request?

        @request = @model.save(displayLoading: yes).done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        @request.always =>
            @request = null
            @update()

        @update()

        this

    close: ->
        if @request
            confirmed = confirm Cruddy.lang.confirm_abort
        else
            confirmed = if @model.hasChangedSinceSync() then confirm(Cruddy.lang.confirm_discard) else yes

        if confirmed
            @request.abort() if @request
            if @inner then @remove() else Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    destroy: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm(Cruddy.lang.confirm_delete) else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    copy: ->
        @model.entity.set "instance", copy = @model.copy()
        Cruddy.router.navigate copy.link()

        this

    render: ->
        @dispose()

        @$el.html @template()

        @nav = @$ ".navbar-nav"
        @footer = @$ "footer"
        @submit = @$ ".btn-save"
        @destroy = @$ ".btn-destroy"
        @copy = @$ ".btn-copy"

        @tabs = []
        @renderTab @model, yes

        # @renderTab related for key, related of @model.related

        @update()

    renderTab: (model, active) ->
        @tabs.push fieldList = new FieldList model: model

        id = "tab-" + model.entity.id
        fieldList.render().$el.insertBefore(@footer).wrap $ "<div></div>", { id: id, class: "wrap" + if active then " active" else "" }
        @nav.append @navTemplate model.entity.get("title").singular, id, active

        this

    update: ->
        permit = @model.entity.getPermissions()

        @$el.toggleClass "loading", @request?

        @submit.text if @model.isNew() then Cruddy.lang.create else Cruddy.lang.save
        @submit.attr "disabled", @request?
        @submit.toggle if @model.isNew() then permit.create else permit.update

        @destroy.attr "disabled", @request?
        @destroy.html if @model.entity.isSoftDeleting() and @model.get "deleted_at" then "Восстановить" else "<span class='glyphicon glyphicon-trash' title='#{ Cruddy.lang.delete }'></span>"
        @destroy.toggle not @model.isNew() and permit.delete
        
        @copy.toggle not @model.isNew() and permit.create

        this

    template: ->
        """
        <div class="navbar navbar-default navbar-static-top" role="navigation">
            <button type="button" tabindex="-1" class="btn btn-link btn-copy navbar-btn pull-right" title="#{ Cruddy.lang.copy }">
                <span class="glyphicon glyphicon-book"></span>
            </button>

            <ul class="nav navbar-nav"></ul>
        </div>

        <footer>
            <button type="button" class="btn btn-default btn-close" type="button">#{ Cruddy.lang.close }</button>
            <button type="button" class="btn btn-default btn-destroy" type="button"></button>
            <button type="button" class="btn btn-primary btn-save" type="button" disabled></button>
        </footer>
        """

    navTemplate: (label, target, active) ->
        active = if active then " class=\"active\"" else ""
        """
        <li#{ active }><a href="##{ target }" data-toggle="tab">#{ label }</a></li>
        """

    remove: ->
        @trigger "remove", @
        
        @$el.one(TRANSITIONEND, =>
            @dispose()

            $(document).off "." + @cid

            @trigger "removed", @

            super
        )
        .removeClass "opened"

        this

    dispose: ->
        fieldList.remove() for fieldList in @tabs if @tabs?

        this