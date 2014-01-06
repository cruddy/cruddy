# View that displays a form for an entity instance
class EntityForm extends Backbone.View
    className: "entity-form"

    events:
        "click .btn-save": "save"
        "click .btn-close": "close"
        "click .btn-destroy": "destroy"

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.entity.id

        super

    initialize: ->
        @listenTo @model, "destroy", @handleDestroy

        @signOn @model
        @signOn related for key, related of @model.related

        @hotkeys = $(document).on "keydown." + @cid, "body", $.proxy this, "hotkeys"

        this

    signOn: (model) ->
        @listenTo model, "change", @enableSubmit
        @listenTo model, "invalid", @displayInvalid

    hotkeys: (e) ->
        # Ctrl + Z
        if e.ctrlKey and e.keyCode is 90
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

    enableSubmit: ->
        @submit.attr "disabled", @model.hasChangedSinceSync() is no

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

    displaySuccess: (resp) -> @displayAlert "Получилось!", "success"

    displayInvalid: -> @displayAlert "Не получилось...", "warning"

    displayError: (xhr) -> @displayAlert "Ошибка", "danger" unless xhr.responseJSON? and xhr.responseJSON.error is "VALIDATION"

    handleDestroy: ->
        if @model.entity.get "soft_deleting"
            @update()
        else
            Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    show: ->
        setTimeout (=>
            @$el.toggleClass "opened", true
            @tabs[0].focus()
        ), 50

        this

    save: ->
        return if @request? or not @model.hasChangedSinceSync()

        @request = @model.save().then $.proxy(this, "displaySuccess"), $.proxy(this, "displayError")

        @request.always =>
            @request = null
            @update()

        @update()

        this

    close: ->
        if @request
            confirmed = confirm "Вы точно хотите закрыть форму и отменить операцию?"
        else
            confirmed = if @model.hasChangedSinceSync() then confirm("Вы точно хотите закрыть форму? Все изменения будут утеряны!") else yes

        if confirmed
            @request.abort() if @request
            Cruddy.router.navigate @model.entity.link(), trigger: true

        this

    destroy: ->
        return if @request or @model.isNew()

        softDeleting = @model.entity.get "soft_deleting"

        confirmed = if not softDeleting then confirm("Точно удалить? Восстановить не получится!") else yes

        if confirmed
            @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true

            @request.always => @request = null

        this

    render: ->
        @dispose()

        @$el.html @template()

        @nav = @$ ".nav"
        @footer = @$ "footer"
        @submit = @$ ".btn-save"
        @destroy = @$ ".btn-destroy"

        @tabs = []
        @renderTab @model, yes

        @renderTab related for key, related of @model.related

        @update()

    renderTab: (model, active) ->
        @tabs.push fieldList = new FieldList model: model

        id = "tab-" + model.entity.id
        fieldList.render().$el.insertBefore(@footer).wrap $ "<div></div>", { id: id, class: "wrap" + if active then " active" else "" }
        @nav.append @navTemplate model.entity.get("singular"), id, active

        this

    update: ->
        @$el.toggleClass "loading", @request?

        @submit.text if @model.isNew() then "Создать" else "Сохранить"
        @submit.attr "disabled", @model.hasChangedSinceSync() is no or @request is on
        @submit.toggle @model.entity.get if @model.isNew() then "can_create" else "can_update"

        @destroy.attr "disabled", @request is on
        @destroy.text if @model.entity.get "soft_deleting" and @model.get "deleted_at" then "Восстановить" else "Удалить"
        @destroy.toggle not @model.isNew() and @model.entity.get "can_delete"

        this

    template: ->
        """
        <header>
            <ul class="nav nav-pills"></ul>
        </header>

        <footer>
            <button class="btn btn-default btn-close btn-sm" type="button">Закрыть</button>
            <button class="btn btn-default btn-destroy btn-sm" type="button">Удалить</button>
            <button class="btn btn-primary btn-save btn-sm" type="button" disabled></button>
        </footer>
        """

    navTemplate: (label, target, active) ->
        active = if active then " class=\"active\"" else ""
        """
        <li#{ active }><a href="##{ target }" data-toggle="tab">#{ label }</a></li>
        """

    remove: ->
        @$el.one(TRANSITIONEND, =>
            @dispose()

            $(document).off "." + @cid

            super
        )
        .removeClass "opened"

        this

    dispose: ->
        fieldList.remove() for fieldList in @tabs if @tabs?

        this