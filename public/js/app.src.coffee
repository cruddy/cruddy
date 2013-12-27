Cruddy = window.Cruddy || {}

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

$(document).ajaxError (e, xhr) =>
    location.href = "login" if xhr.status == 403
humanize = (id) => id.replace(/_-/, " ")

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this
class Factory
    types: {}

    register: (name, constructor) -> @types[name] = constructor

    create: (name, options) ->
        constructor = @types[name]
        new constructor options if constructor?
class Attribute extends Backbone.Model
class DataSource extends Backbone.Model
    defaults:
        data: []

    initialize: (attributes, options) ->
        @entity = options.entity
        @columns = options.columns if options.columns?
        @filter = options.filter if options.filter?

        @listenTo @filter, "change", @fetch if @filter?

        @on "change", => @fetch() unless @_fetching

    hasData: -> not _.isEmpty @get "data"

    isFull: -> @get("current_page") == @get("last_page")

    fetch: ->
        @request.abort() if @request?

        @request = $.getJSON "#{ API_URL }/#{ @entity.id }", @data(), (resp) =>
            @_fetching = true
            @set resp.data
            @_fetching = false
            @trigger "data", this, resp.data.data

        @request.fail (xhr) => @trigger "error", this, xhr
        @request.always => @request = null

        @trigger "request", this, @request

        @request

    data: ->
        data = {
            order_by: @get "order_by"
            order_dir: @get "order_dir"
            page: @get "current_page"
            per_page: @get "per_page"
        }

        filters = @filterData()

        data.filters = filters unless _.isEmpty filters
        data.columns = @columns.join "," if @columns?

        data

    filterData: ->
        return null unless @filter?

        data = {}

        for key, value of @filter.attributes
            data[key] = value unless value is null or value is ""

        data
class DataGrid extends Backbone.View
    tagName: "table"
    className: "table table-hover table-condensed data-grid"

    events: {
        "click .sortable": "setOrder"
        "click .item": "navigate"
    }

    constructor: (options) ->
        @className += " data-grid-" + options.model.entity.id

        super

    initialize: (options) ->
        @entity = @model.entity

        @listenTo @model, "change:data", @updateData
        @listenTo @model, "change:order_by change:order_dir", @onOrderChange

        @listenTo @entity, "change:instance", @onInstanceChange

    onOrderChange: ->
        orderBy = @model.get "order_by"
        orderDir = @model.get "order_dir"

        if @orderBy? and orderBy isnt @orderBy
            @$("#col-#{ @orderBy } .sortable").removeClass "asc desc"

        @orderBy = orderBy
        @$("#col-#{ @orderBy } .sortable").removeClass("asc desc").addClass orderDir

        this

    onInstanceChange: (entity, curr) ->
        prev = entity.previous "instance"

        if prev?
            @$("#item-#{ prev.id }").removeClass "active"
            prev.off null, null, this

        if curr?
            @$("#item-#{ curr.id }").addClass "active"
            curr.on "sync destroy", (=> @model.fetch()), this

        this

    setOrder: (e) ->
        orderBy = $(e.target).data "id"
        orderDir = @model.get "order_dir"

        if orderBy is @model.get "order_by"
            orderDir = if orderDir == 'asc' then 'desc' else 'asc'
        else
            orderDir = @entity.columns.get(orderBy).get "order_dir"

        @model.set { order_by: orderBy, order_dir: orderDir }

        this

    navigate: (e) ->
        Cruddy.router.navigate @entity.link($(e.currentTarget).data "id"), { trigger: true }

        this

    updateData: (datasource, data) ->
        @$(".items").replaceWith @renderBody @entity.columns.models, data

        this

    render: ->
        columns = @entity.columns.models
        data = @model.get "data"

        @$el.html @renderHead(columns) + @renderBody(columns, data)

        @onOrderChange @model

        this

    renderHead: (columns) ->
        html = "<thead><tr>"
        html += @renderHeadCell col for col in columns
        html += "</tr></thead>"

    renderHeadCell: (col) ->
        """<th class="#{ col.getClass() }" id="col-#{ col.id }">#{ col.renderHeadCell() }</th>"""

    renderBody: (columns, data) ->
        html = "<tbody class=\"items\">"

        if data? and data.length
            html += @renderRow columns, item for item in data
        else
            html += """<tr><td class="no-items" colspan="#{ columns.length }">Ничего не найдено</td></tr>"""

        html += "</tbody>"

    renderRow: (columns, item) ->
        instance = @entity.get "instance"
        active = if instance? and item.id == instance.id then "active" else ""

        html = "<tr class=\"item #{ active }\" id=\"item-#{ item.id }\" data-id=\"#{ item.id }\">"
        html += @renderCell col, item for col in columns
        html += "</tr>"

    renderCell: (col, item) ->
        """<td class="#{ col.getClass() }">#{ col.renderCell item[col.id] }</td>"""
# Displays a list of entity's fields
class FieldList extends Backbone.View
    className: "field-list"

    initialize: ->
        @listenTo @model.entity.fields, "add remove", @render

        this

    focus: ->
        @primary?.focus()

        this

    render: ->
        @$el.empty()

        @$el.append field.el for field in @createFields()

        this

    createFields: ->
        @dispose()

        @fields = (field.createView(@model).render() for field in @model.entity.fields.models)
        @primary = null

        for view in @fields when view.field.isEditable @model
            @primary = view
            break

        @fields

    dispose: ->
        field.remove() for field in @fields if @fields?

        this

    stopListening: ->
        @dispose()

        super
class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    initialize: (options) ->
        @entity = options.entity

        this

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        @filters = []
        for col in @entity.columns.models when col.get "filterable"
            if input = col.createFilterInput @model
                @filters.push input
                @items.append input.render().el
                input.$el.wrap("""<div class="form-group filter #{ col.getClass() }"><div class="input-wrap"></div></div>""").parent().before "<label>#{ col.get "title" }</label>"

        this

    template: -> """<div class="filter-list-container"></div>"""

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        this

    remove: ->
        @dispose()

        super
# Base class for input that will be bound to a model's attribute.
class BaseInput extends Backbone.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, @applyChanges

        this

    # Apply changes when model's attribute changed.
    applyChanges: (model, data) -> this

    render: -> @applyChanges @model, @model.get @key

    focus: -> this
# Renders formatted text and doesn't have any editing features.
class StaticInput extends BaseInput
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    applyChanges: (model, data) -> @render()

    render: ->
        value = @model.get @key
        value = @formatter.format value if @formatter?

        @$el.html value

        this
# Renders an <input> value of which is bound to a model's attribute.
class TextInput extends BaseInput
    tagName: "input"

    className: "form-control"
    size: "sm"

    events:
        "change": "change"
        "keydown": "keydown"

    constructor: (options) ->
        @size = options.size if options.size?

        @className += " input-#{ @size }"

        super

    scheduleChange: ->
        clearTimeout @timeout if @timeout?
        @timeout = setTimeout (=> @change()), 300

        this

    keydown: (e) ->
        # Ctrl + Enter
        if e.ctrlKey and e.keyCode is 13
            @change()
            return false

        # Escape
        if e.keyCode is 27
            @model.set @key, ""
            return false

    change: ->
        @model.set @key, @el.value

        this

    applyChanges: (model, data) ->
        @$el.val data

        this

    focus: ->
        @el.focus()

        this

# Renders a <textarea> input.
class Textarea extends TextInput
    tagName: "textarea"
# Renders a checkbox
class Checkbox extends BaseInput
    tagName: "label"
    label: ""

    events:
        "change": "change"

    initialize: (options) ->
        @label = options.label if options.label?

        super

    change: ->
        @model.set @key, @input.prop "checked"

        this

    applyChanges: (model, value) ->
        @input.prop "checked", value

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @model.get @key }
        @$el.append @input
        @$el.append @label if @label?

        this
class BooleanInput extends BaseInput
    tripleState: false

    events:
        "click input": "check"

    initialize: (options) ->
        @tripleState = options.tripleState if options.tripleState?

    check: (e) ->
        @model.set @key, switch e.target.value
            when "1" then yes
            when "0" then no
            else null

        this

    applyChanges: (model, value) ->
        value = switch value
            when yes then "1"
            when no then "0"
            else ""

        @$("[value=\"#{ value }\"]").prop "checked", true

        this

    render: ->
        @$el.empty()
        @$el.append @itemTemplate "неважно", "" if @tripleState
        @$el.append @itemTemplate "да", 1
        @$el.append @itemTemplate "нет", 0

        super

    itemTemplate: (label, value) -> """
        <label class="radio-inline">
            <input type="radio" name="#{ @cid }" value="#{ value }">
            #{ label }
        </label>
        """
class EntityDropdown extends BaseInput
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "show.bs.dropdown": "renderDropdown"

    mutiple: false
    reference: null

    initialize: (options) ->
        @multiple = options.multiple if options.multiple?
        @reference = options.reference if options.reference?
        @active = false

        super

    removeItem: (e) ->
        if @multiple
            i = $(e.currentTarget).data "key"
            value = _.clone @model.get(@key)
            value.splice i, 1
        else
            value = null

        @model.set @key, value

        this

    renderDropdown: ->
        return if @selector?

        @selector = new EntitySelector
            model: @model
            key: @key
            multiple: @multiple
            reference: @reference

        @dropdown = $ "<div></div>", class: "selector-wrap"

        @$el.append @dropdown.append @selector.render().el

        this


    applyChanges: (model, value) ->
        if @multiple
            @renderItems()
        else
            @updateItem()
            @$el.removeClass "open"

        this

    render: ->
        @dispose()

        if @multiple then @renderMultiple() else @renderSingle()

        @$el.attr "id", @cid

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-sm btn-block dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }">
                Выбрать
                <span class="caret"></span>
            </button>
            """

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate value.title, key for value, key in @model.get @key
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->

        @$el.html @itemTemplate "", ""

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"

        @updateItem()

    updateItem: ->
        value = @model.get @key
        @itemTitle.text if value then value.title else "Не выбрано"
        @itemDelete.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
        <div class="input-group input-group-sm item">
            <p class="form-control">#{ _.escape value }</p>
            <div class="input-group-btn">
        """

        if not @multiple or key isnt null
            html += """
                <button type="button" class="btn btn-default btn-remove" data-key="#{ key }">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
                """

        if not @multiple or key is null
            html += """
                <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" data-target="##{ @cid }">
                    <span class="caret"></span>
                </button>
                """

        html += "</div></div>"

    dispose: ->
        @selector.stopListening() if @selector
        @selector = null

        this

    stopListening: ->
        @dispose()

        super
class EntitySelector extends BaseInput
    className: "entity-selector"

    events:
        "click li": "check"

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false

        @data = []
        @buildSelected @model.get @key

        Cruddy.app.entity(options.reference).then (entity) =>
            @entity = entity
            @primaryKey = "id"
            @primaryColumn = entity.get "primary_column"

            @dataSource = entity.search()

            @listenTo @dataSource, "request",   @loading
            @listenTo @dataSource, "data",      @appendItems

        this

    check: (e) ->
        id = parseInt $(e.target).data "id"
        uncheck = id of @selected
        item = _.find @data, (item) -> item.id == id

        if @multiple
            if uncheck
                value = _.filter @model.get(@key), (item) -> item.id != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @model.set @key, value

        false

    applyChanges: (model, data) ->
        @buildSelected data
        @renderItems()

    buildSelected: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    loading: -> this

    appendItems: (datasource, data) ->
        return if _.isEmpty data

        @data.push { id: item[@primaryKey], title: item[@primaryColumn] } for item in data

        @renderItems()

        this

    renderItems: ->
        html = ""
        html += @renderItem item for item in @data
        @items.html html

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="#{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        @dispose()

        @$el.html @template()

        @items = @$ ".items"

        @appendItems @dataSource, @dataSource.get "data" if @dataSource? and @dataSource.hasData()

        this

    template: -> """<div class="items-container"><ul class="items"></ul></div>"""

    dispose: ->
        this

    stopListening: ->
        @dispose()

        super

Cruddy.fields = new Factory

class FieldView extends Backbone.View
    className: "field"

    constructor: (options) ->
        @inputId = options.model.entity.id + "_" + options.field.id

        base = " " + @className + "-"
        classes = [ options.field.attributes.type, options.field.id, @inputId ]
        @className += base + classes.join base

        @className += " required" if options.field.get "required"

        super

    initialize: (options) ->
        @field = options.field

        @listenTo @field, "change:visible",     @toggleVisibility
        @listenTo @field, "change:editable",    @render

        @listenTo @model, "sync",       @render
        @listenTo @model, "request",    @hideError
        @listenTo @model, "invalid",    @showError

        this

    hideError: ->
        @error.hide()
        @inputHolder.removeClass "has-error"

    showError: (model, errors) ->
        error = errors[@field.get "id"]

        if error
            @inputHolder.addClass "has-error"
            @error.text(error).show()

    # Render a field.
    render: ->
        @input.remove() if @input?

        @$el.html @template()

        @inputHolder = @$ ".input-holder"

        @input = @field.createInput @model
        @inputHolder.append @input.render().el if @input?

        @inputHolder.append @error = $ @errorTemplate()

        @toggleVisibility()

        this

    helpTemplate: ->
        help = @field.get "help"
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error"></span>"""

    label: (label) ->
        label ?= @field.get "label"
        """<label for="#{ @inputId }">#{ label }</label>"""

    # The default template that is shown when field is editable.
    template: ->
        """
        #{ @helpTemplate() }
        <div class="form-group input-holder">
            #{ @label() }
        </div>
        """

    # Get whether this field view is visible.
    isVisible: -> @field.get("visible") and (@field.get("editable") and @field.get("updatable") or not @model.isNew())

    toggleVisibility: -> @$el.toggle @isVisible()

    # Focus the input that this field view holds.
    focus: ->
        @input.focus() if @input?
        this

    stopListening: ->
        @input.stopListening() if @input?

        super

class Field extends Attribute
    viewConstructor: FieldView

    createView: (model) -> new @viewConstructor { model: model, field: this }

    createInput: (model) ->
        input = @createEditableInput model if @isEditable model

        if input? then input else new StaticInput { model: model, key: @id, formatter: this }

    createEditableInput: (model) -> null

    format: (value) -> if value then value else "n/a"

    isEditable: (model) -> @get("editable") and (@get("updatable") or not model.isNew()) and model.isSaveable()
class Cruddy.fields.Input extends Field
    createEditableInput: (model) ->
        attributes = placeholder: @get "label"
        type = @get "input_type"

        if type is "textarea"
            attributes.rows = @get "rows"

            new Textarea
                model: model
                key: @id
                attributes: attributes
        else
            attributes.type = type

            new TextInput
                model: model
                key: @id
                attributes: attributes

    format: (value) -> if @get("input_type") is "textarea" then "<pre>#{ super }</pre>" else super

    createFilterInput: (model, column) ->
        new TextInput
                model: model
                key: @id
                attributes:
                    placeholder: @get "label"

Cruddy.fields.register "Input", Cruddy.fields.Input
# DATE AND TIME FIELD TYPE

###
class Cruddy.fields.DateTimeView extends Cruddy.fields.InputView
    format: (value) -> moment.unix(value).format @field.get "format"
    unformat: (value) -> moment(value, @field.get "format").unix()
###

class Cruddy.fields.DateTime extends Cruddy.fields.Input
    #viewConstructor: Cruddy.fields.DateTimeView

    format: (value) -> if value is null then "никогда" else moment.unix(value).calendar()

Cruddy.fields.register "DateTime", Cruddy.fields.DateTime
class Cruddy.fields.Boolean extends Field
    createEditableInput: (model) -> new BooleanInput { model: model, key: @id }

    createFilterInput: (model) -> new BooleanInput { model: model, key: @id, tripleState: yes }

    format: (value) -> if value then "да" else "нет"

Cruddy.fields.register "Boolean", Cruddy.fields.Boolean
class Cruddy.fields.Relation extends Field
    createEditableInput: (model) ->
        new EntityDropdown
            model: model
            key: @id
            multiple: @get "multiple"
            reference: @get "reference"

    createFilterInput: (model) -> @createEditableInput model

    format: (value) ->
        return "не указано" if _.isEmpty value
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title

Cruddy.fields.register "Relation", Cruddy.fields.Relation
Cruddy.columns = new Factory

class Column extends Attribute
    renderHeadCell: ->
        title = @get "title"
        help = @get "help"
        title = "<span class=\"sortable\" data-id=\"#{ @id }\">#{ title }</span>" if @get "sortable"
        if help then "<span class=\"glyphicon glyphicon-question-sign\" title=\"#{ help }\"></span> #{ title }" else title

    renderCell: (value) -> value

    createFilterInput: (model) -> null

    getClass: -> "col-" + @id


class Cruddy.columns.Field extends Column
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "title", @field.get "label" if attributes.title is null

        super

    renderCell: (value) -> @field.format value

    createFilterInput: (model) -> @field.createFilterInput model, this

    getClass: -> super + " col-" + @field.get "type"

Cruddy.columns.register "Field", Cruddy.columns.Field

class Cruddy.columns.Computed extends Column
    createFilterInput: (model) ->
        new TextInput
            model: model
            key: @id
            attributes:
                placeholder: @get "title"

    getClass: -> super + " col-computed"

Cruddy.columns.register "Computed", Cruddy.columns.Computed
class Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.fields, attributes.fields
        @columns = @createCollection Cruddy.columns, attributes.columns

        @related = {}
        @related[item.related] = new Related item for item in attributes.related

        @set "label", humanize @id if @get("label") is null

    createCollection: (factory, items) ->
        data = []
        for options in items
            options.entity = this
            instance = factory.create options.class, options
            data.push instance if instance?

        new Backbone.Collection data

    # Create a datasource that will require specified columns and can be filtered
    # by specified filters
    createDataSource: (columns = null) ->
        data = { order_by: @get("order_by") || @get("primary_column") }
        data.order_dir = if data.order_dir? then @columns.get(data.order_by).get "order_dir" else "asc"

        new DataSource data, { entity: this, columns: columns, filter: new Backbone.Model }

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get "filterable")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, related = null) ->
        related = (item.related.createInstance() for key, item of @related) if related is null

        new EntityInstance _.extend({}, @get("defaults"), attributes), { entity: this, related: related }

    search: ->
        @searchInstance = @createDataSource ["id", @get "primary_column"] if not @searchInstance?
        @searchInstance.set "current_page", 1

        @searchInstance

    # Load an instance and set it as currently active.
    update: (id) ->
        $.getJSON("#{ API_URL }/#{ @id }/#{ id }").then (resp) =>
            #@fields.set resp.data.runtime, add: false

            related = (item.related.createInstance resp.data.related[item.id] for key, item of @related)

            @set "instance", instance = @createInstance resp.data.instanceData, related

            instance

    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""
class EntityInstance extends Backbone.Model
    initialize: (attributes, options) ->
        @entity = options.entity
        @related = options.related
        @original = _.clone attributes

        @on "error", @processError, this
        @on "sync", => @original = _.clone @attributes
        @on "destroy", => @set "deleted_at", moment().unix() if @entity.get "soft_deleting"

    processError: (model, xhr) ->
        @trigger "invalid", this, xhr.responseJSON.data if xhr.responseJSON? and xhr.responseJSON.error is "VALIDATION"

    validate: ->
        @set "errors", {}
        null

    link: -> @entity.link @id

    url: ->
        url = "#{ API_URL }/#{ @entity.id }"
        if @isNew() then url else url + "/" + @id

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new FormData
            @append options.data, @entity.id, options.attrs ? @attributes

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    save: ->
        xhr = super

        return xhr if _.isEmpty @related

        queue = (xhr) =>
            save = []

            save.push xhr if xhr?

            for related in @related
                @entity.related[related.entity.id].associate @, related if related.isNew()

                save.push related.save()

            $.when.apply save

        # Create related models after the main model is saved
        if @isNew() then xhr.then (resp) -> queue() else queue xhr

    append: (data, key, value) ->
        if value instanceof File
            data.append key, value
            return

        if _.isArray value
            return @append data, key, "" if value.length == 0

            @append data, key + "[" + i + "]", _value for _value, i in value

            return

        if _.isObject value
            @append data, key + "[" + _key + "]", _value for _key, _value of value

            return

        data.append key, @convertValue value

        this

    convertValue: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    parse: (resp) -> resp.data

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when not _.isEqual value, @original[key]

        # Related models do not affect the result unless model is created
        return yes for related in @related when related.hasChangedSinceSync() unless @isNew()

        no

    isSaveable: -> (@isNew() and @entity.get("can_create")) or (!@isNew() and @entity.get("can_update"))
class EntityPage extends Backbone.View
    className: "entity-page"

    events: {
        "click .btn-create": "create"
    }

    constructor: (options) ->
        @className += " " + @className + "-" + options.model.id

        super

    initialize: (options) ->
        @listenTo @model, "change:instance", @toggleForm

        super

    toggleForm: (entity, instance) ->
        if @form?
            @stopListening @form.model
            @form.remove()

        if instance?
            @listenTo instance, "sync", -> Cruddy.router.navigate instance.link()

            @form = new EntityForm model: instance
            @$el.append @form.render().$el
            @form.show()

        this

    create: ->
        Cruddy.router.navigate @model.link("create"), trigger: true

        this

    render: ->
        @dispose()

        @$el.html @template()

        @dataSource = @model.createDataSource()

        @dataGrid = new DataGrid
            model: @dataSource

        @filterList = new FilterList
            model: @dataSource.filter
            entity: @dataSource.entity

        @dataSource.fetch()

        @$el.append @filterList.render().el
        @$el.append @dataGrid.render().el

        this

    template: ->
        html = """
        <h1 class="page-header">
            #{ @model.get "title" }

        """

        if @model.get "can_create"
            html += """
                <button class="btn btn-default btn-create" type="button">
                    <span class="glyphicon glyphicon-plus"</span>
                </button>
            """

        html += "</h1>"

    dispose: ->
        @form.remove() if @form?
        @filterList.remove() if @filterList?
        @dataGrid.remove() if @dataGrid?
        @dataSource.stopListening() if @dataSource?

        this

    remove: ->
        @dispose()

        super
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
        @signOn related for related in @model.related

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

        @request = if @softDeleting and @model.get "deleted_at" then @model.restore else @model.destroy wait: true if confirmed

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

        @renderTab related for related in @model.related

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
class Related extends Backbone.Model
    resolve: -> Cruddy.app.entity(@get "related").then (entity) => @related = entity

    associate: (parent, child) -> child.set @get("foreign_key"), parent.id
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