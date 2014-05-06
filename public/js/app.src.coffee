Cruddy = window.Cruddy || {}

Cruddy.baseUrl = Cruddy.root + "/" + Cruddy.uri

API_URL = "/backend/api/v1"
TRANSITIONEND = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd"
moment.lang Cruddy.locale ? "en"

Backbone.emulateHTTP = true
Backbone.emulateJSON = true

#$(document).ajaxError (e, xhr, options) =>
#    location.href = "/login" if xhr.status is 403 and not options.dontRedirect

$(document)
    .ajaxSend((e, xhr, options) -> Cruddy.app.startLoading() if options.displayLoading)
    .ajaxComplete((e, xhr, options) -> Cruddy.app.doneLoading() if options.displayLoading)

$.extend $.fancybox.defaults,
    openEffect: "elastic"
humanize = (id) => id.replace(/_-/, " ")

# Get url for an entity action
entity_url = (id, extra) ->
    url = Cruddy.baseUrl + "/api/" + id;
    url += "/" + extra if extra

    url

# Call callback after browser has taken a breath
after_break = (callback) -> setTimeout callback, 50

# Get thumb link
thumb = (src, width, height) ->
    url = "#{ Cruddy.baseUrl }/thumb?src=#{ encodeURIComponent(src) }"
    url += "&amp;width=#{ width }" if width
    url += "&amp;height=#{ height }" if height

    url

b_icon = (icon) -> "<span class='glyphicon glyphicon-#{ icon }'></span>"

b_btn = (label, icon = null, className = "btn-default", type = 'button') ->
    label = b_icon(icon) + ' ' + label if icon
    className = "btn-" + className.join(" btn-") if _.isArray className

    "<button type='#{ type }' class='btn #{ className }'>#{ label.trim() }</button>"

class Alert extends Backbone.View
    tagName: "span"
    className: "alert"

    initialize: (options) ->
        @$el.addClass @className + "-" + options.type ? "info"
        @$el.text options.message

        setTimeout (=> @remove()), options.timeout if options.timeout?

        this

    render: ->
        after_break => @$el.addClass "show"

        this

    remove: ->
        @$el.one TRANSITIONEND, => super

        @$el.removeClass "show"

        this
class Cruddy.View extends Backbone.View
    componentId: (component) -> @cid + "-" + component

    $component: (component) -> @$ "#" + @componentId(component)
class AdvFormData
    constructor: (data) ->
        @original = new FormData
        @append data if data?

    append: (name, value) ->
        if value is undefined
            value = name
            name = null

        return @original.append name, value if value instanceof File or value instanceof Blob

        if _.isArray value
            return @append name, "" if _.isEmpty value

            @append @key(name, key), _value for _value, key in value

            return

        if _.isObject value
            if _.isFunction value.serialize
                @append name, value.serialize()
                
            else
                @append @key(name, key), _value for key, _value of value

            return

        @original.append name, @process value

    process: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    key: (outer, inner) -> if outer then "#{ outer }[#{ inner }]" else inner
class Factory
    create: (name, options) ->
        constructor = @[name]
        return new constructor options if constructor?

        console.error "Failed to resolve #{ name }."

        null
class Attribute extends Backbone.Model

    initialize: (options) ->
        @entity = options.entity

        this

    # Get field's type (i.e. css class name)
    getType: -> @attributes.type

    # Get field's help
    getHelp: -> @attributes.help

    # Get whether a column has complex filter
    canFilter: -> @attributes.filter_type is "complex"

    # Get whether a column is visible
    isVisible: -> @attributes.hide is no
class DataSource extends Backbone.Model
    defaults:
        data: []
        search: ""

    initialize: (attributes, options) ->
        @entity = options.entity
        @columns = options.columns if options.columns?
        @filter = options.filter if options.filter?

        @options =
            url: @entity.url()
            dataType: "json"
            type: "get"
            displayLoading: yes

            success: (resp) =>
                @_hold = true
                @set resp.data
                @_hold = false

                @trigger "data", this, resp.data.data

            error: (xhr) => @trigger "error", this, xhr

        @listenTo @filter, "change", (=>
            @set current_page: 1, silent: yes
            @fetch()
        ) if @filter?

        @on "change", => @fetch() unless @_hold
        @on "change:search", => @set current_page: 1, silent: yes

    hasData: -> not _.isEmpty @get "data"

    hasMore: -> @get("current_page") < @get("last_page")

    isFull: -> !@hasMore()

    inProgress: -> @request?

    fetch: ->
        @request.abort() if @request?

        @options.data = @data()

        @request = $.ajax @options

        @request.always => @request = null

        @trigger "request", this, @request

        @request

    more: ->
        return if @isFull()

        @set current_page: @get("current_page") + 1, silent: yes

        @fetch()

    data: ->
        data = {
            order_by: @get "order_by"
            order_dir: @get "order_dir"
            page: @get "current_page"
            per_page: @get "per_page"
            keywords: @get "search"
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
class SearchDataSource extends Backbone.Model
    defaults:
        search: ""

    initialize: (attributes, options) ->
        @filters = new Backbone.Model

        @options =
            url: options.url
            type: "get"
            dataType: "json"

            data:
                simple: 1

            success: (resp) =>
                resp = resp.data

                @data.push item for item in resp.data

                @page = resp.current_page
                @more = resp.current_page < resp.last_page
                @request = null

                @trigger "data", this, @data

                this

            error: (xhr) =>
                @request = null
                @trigger "error", this, xhr

                this

        $.extend yes, @options, options.ajaxOptions if options.ajaxOptions?

        @reset()

        @on "change:search", @refresh, this
        @listenTo @filters, "change", @refresh

        this

    refresh: -> @reset().next()

    reset: ->
        @data = []
        @page = null
        @more = yes

        this

    fetch: (q, page, filters) ->
        @request.abort() if @request?

        $.extend @options.data, 
            page: page
            keywords: q
            filters: filters

        @trigger "request", this, @request = $.ajax @options

        @request

    next: ->
        if @more
            page = if @page? then @page + 1 else 1

            @fetch @get("search"), page, @filters.attributes

        this

    inProgress: -> @request?
class Pagination extends Backbone.View
    tagName: "ul"
    className: "pager"

    events:
        "click a": "navigate"

    initialize: (options) ->
        @listenTo @model, "data", @render
        @listenTo @model, "request", @disable

        $(document).on "keydown.pagination", $.proxy this, "hotkeys"

        this

    hotkeys: (e) ->
        if e.ctrlKey and e.keyCode is 37
            @previous()

            return false

        if e.ctrlKey and e.keyCode is 39
            @next()

            return false

        this

    page: (n) ->
        @model.set "current_page", n if n > 0 and n <= @model.get "last_page"

        this

    previous: -> @page @model.get("current_page") - 1

    next: -> @page @model.get("current_page") + 1

    navigate: (e) ->
        e.preventDefault()

        @page $(e.target).data "page" if !@model.inProgress()

    disable: ->
        @$("a").addClass "disabled"

        this

    render: ->
        last = @model.get("last_page")

        @$el.toggle last? and last > 1

        @$el.html @template @model.get("current_page"), last if last > 1

        this

    template: (current, last) ->
        html = ""
        html += @renderLink current - 1, "&larr; #{ Cruddy.lang.prev }", "previous" + if current > 1 then "" else " disabled"
        html += @renderStats() if @model.get("total")?
        html += @renderLink current + 1, "#{ Cruddy.lang.next } &rarr;", "next" + if current < last then "" else " disabled"

        html

    renderStats: -> """<li class="stats"><span>#{ @model.get "from" } - #{ @model.get "to" } / #{ @model.get "total" }</span></li>"""

    renderLink: (page, label, className = "") -> """<li class="#{ className }"><a href="#" data-page="#{ page }">#{ label }</a></li>"""

class DataGrid extends Backbone.View
    tagName: "table"
    className: "table table-hover data-grid"

    events: {
        "click .sortable": "setOrder"
        "click .item": "navigate"
    }

    constructor: (options) ->
        @className += " data-grid-" + options.entity.id

        super

    initialize: (options) ->
        @entity = options.entity
        @columns = @entity.columns.models.filter (col) -> col.isVisible()

        @listenTo @model, "data", @updateData
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
        @$(".items").replaceWith @renderBody @columns, data

        this

    render: ->
        data = @model.get "data"

        @$el.html @renderHead(@columns) + @renderBody(@columns, data)

        @onOrderChange @model

        this

    renderHead: (columns) ->
        html = "<thead><tr>"
        html += @renderHeadCell col for col in columns
        html += "</tr></thead>"

    renderHeadCell: (col) ->
        """<th class="#{ col.getClass() }" id="col-#{ col.id }">#{ @renderHeadCellValue col }</th>"""

    renderHeadCellValue: (col) ->
        title = _.escape col.getHeader()
        help = _.escape col.getHelp()
        title = "<span class=\"sortable\" data-id=\"#{ col.id }\">#{ title }</span>" if col.canOrder()
        if help then "<span class=\"glyphicon glyphicon-question-sign\" title=\"#{ help }\"></span> #{ title }" else title

    renderBody: (columns, data) ->
        html = "<tbody class=\"items\">"

        if data? and data.length
            html += @renderRow columns, item for item in data
        else
            html += """<tr><td class="no-items" colspan="#{ columns.length }">#{ Cruddy.lang.no_results }</td></tr>"""

        html += "</tbody>"

    renderRow: (columns, item) ->
        instance = @entity.get "instance"
        active = if instance? and item.id == instance.id then "active" else ""

        html = "<tr class=\"item #{ active }\" id=\"item-#{ item.id }\" data-id=\"#{ item.id }\">"
        html += @renderCell col, item for col in columns
        html += "</tr>"

    renderCell: (col, item) ->
        """<td class="#{ col.getClass() }">#{ col.format item[col.id] }</td>"""
# Displays a list of entity's fields
class FieldList extends Backbone.View
    className: "field-list"

    # Focus first editable field
    focus: ->
        @primary?.focus()

        this

    render: ->
        @dispose()

        @$el.empty()
        @$el.append field.el for field in @createFields()

        this

    createFields: ->
        @fields = (field.createView(@model).render() for field in @model.entity.fields.models when field.isVisible())

        for view in @fields when view.field.isEditable @model
            @primary = view
            break

        @fields

    dispose: ->
        field.remove() for field in @fields if @fields?

        @fields = null
        @primary = null

        this

    remove: ->
        @dispose()

        super
class FilterList extends Backbone.View
    className: "filter-list"

    tagName: "fieldset"

    initialize: (options) ->
        @entity = options.entity
        @availableFilters = options.filters

        this

    render: ->
        @dispose()

        @$el.html @template()
        @items = @$ ".filter-list-container"

        for filter in @availableFilters when (field = @entity.fields.get filter) and field.canFilter() and (input = field.createFilterInput @model)
            @filters.push input
            @items.append input.render().el
            input.$el.wrap("""<div class="form-group filter filter-#{ field.id }"></div>""").parent().before "<label>#{ field.getFilterLabel() }</label>"

        this

    template: -> """<div class="filter-list-container"></div>"""

    dispose: ->
        filter.remove() for filter in @filters if @filters?

        @filters = []

        this

    remove: ->
        @dispose()

        super
Cruddy.Inputs = {}

# Base class for input that will be bound to a model's attribute.
class Cruddy.Inputs.Base extends Cruddy.View
    constructor: (options) ->
        @key = options.key

        super

    initialize: ->
        @listenTo @model, "change:" + @key, (model, value, options) ->
            @applyChanges value, not options.input or options.input isnt this

        this

    # Apply changes when model's attribute changed.
    # external is true when value is changed not by input itself.
    applyChanges: (data, external) -> this

    render: -> @applyChanges @getValue(), yes

    # Focus an element.
    focus: -> this

    # Get current value.
    getValue: -> @model.get @key

    # Set current value.
    setValue: (value, options = {}) ->
        options.input = this

        @model.set @key, value, options

        this
# Renders formatted text and doesn't have any editing features.
class Cruddy.Inputs.Static extends Cruddy.Inputs.Base
    tagName: "p"
    className: "form-control-static"

    initialize: (options) ->
        @formatter = options.formatter if options.formatter?

        super

    applyChanges: (data) -> @render()

    render: ->
        value = @getValue()
        value = @formatter.format value if @formatter?

        @$el.html value

        this
class Cruddy.Inputs.BaseText extends Cruddy.Inputs.Base
    className: "form-control"

    events:
        "change": "change"
        "keydown": "keydown"

    keydown: (e) ->
        # Ctrl + Enter
        return @change() if e.ctrlKey and e.keyCode is 13

        this

    disable: ->
        @$el.prop "disabled", yes

        this

    enable: ->
        @$el.prop "disabled", no

        this

    change: ->
        @model.set @key, @el.value

        this

    applyChanges: (data, external) ->
        @$el.val data if external

        this

    focus: ->
        @el.focus()

        this
        
# Renders an <input> value of which is bound to a model's attribute.
class Cruddy.Inputs.Text extends Cruddy.Inputs.BaseText
    tagName: "input"

    initialize: (options) ->
        # Apply mask
        options.mask and @$el.mask options.mask

        super

# Renders a <textarea> input.
class Cruddy.Inputs.Textarea extends Cruddy.Inputs.BaseText
    tagName: "textarea"
# Renders a checkbox
class Cruddy.Inputs.Checkbox extends Cruddy.Inputs.Base
    tagName: "label"
    label: ""

    events:
        "change": "change"

    initialize: (options) ->
        @label = options.label if options.label?

        super

    change: -> @setValue @input.prop "checked"

    applyChanges: (value, external) ->
        @input.prop "checked", value if external

        this

    render: ->
        @input = $ "<input>", { type: "checkbox", checked: @getValue() }
        @$el.append @input
        @$el.append @label if @label?

        this
class Cruddy.Inputs.Boolean extends Cruddy.Inputs.Base
    events:
        "click .btn": "check"

    initialize: (options) ->
        @tripleState = options.tripleState ? false

        super

    check: (e) ->
        value = !!$(e.target).data "value"
        currentValue = @model.get @key

        value = null if value == currentValue and @tripleState

        @setValue value

    applyChanges: (value) ->
        value = switch value
            when yes then 0
            when no then 1
            else null

        @values.removeClass("active")
        @values.eq(value).addClass "active" if value?

        this

    render: ->
        @$el.html @template()

        @values = @$ ".btn"

        super

    template: ->
        """
        <div class="btn-group">
            <button type="button" class="btn btn-default" data-value="1">#{ Cruddy.lang.yes }</button>
            <button type="button" class="btn btn-default" data-value="0">#{ Cruddy.lang.no }</button>
        </div>
        """
class Cruddy.Inputs.EntityDropdown extends Cruddy.Inputs.Base
    className: "entity-dropdown"

    events:
        "click .btn-remove": "removeItem"
        "click .btn-edit": "editItem"
        "keydown [type=search]": "searchKeydown"
        "show.bs.dropdown": "renderDropdown"

        "shown.bs.dropdown": ->
            after_break => @selector.focus()

            this

        "hidden.bs.dropdown": ->
            @opened = no

            this

    mutiple: false
    reference: null

    initialize: (options) ->
        @multiple = options.multiple if options.multiple?
        @reference = options.reference if options.reference?
        @owner = options.owner if options.owner?
        @allowEdit = options.allowEdit ? yes and @reference.updatePermitted()
        @active = false
        @placeholder = options.placeholder ? Cruddy.lang.not_selected
        @disableDropdown = false

        if options.constraint
            @constraint = options.constraint
            @listenTo @model, "change:" + @constraint.field, -> @checkToDisable().applyConstraint yes

        super

    getKey: (e) -> $(e.currentTarget).closest(".ed-item").data "key"

    removeItem: (e) ->
        if @multiple
            i = @getKey e
            value = _.clone @model.get(@key)
            value.splice i, 1
        else
            value = null

        @setValue value

    editItem: (e) ->
        item = @model.get @key
        item = item[@getKey e] if @multiple

        return if not item

        target = $(e.currentTarget).prop "disabled", yes

        xhr = @reference.load(item.id).done (instance) =>
            @innerForm = new Cruddy.Entity.Form
                model: instance
                inner: yes

            @innerForm.render().$el.appendTo document.body
            after_break => @innerForm.show()

            @listenTo instance, "sync", (model, resp) =>
                # Check whether the model was destroyed
                if resp.data
                    target.parent().siblings("input").val resp.data.title
                    @innerForm.remove()
                else
                    @removeItem e

            @listenTo @innerForm, "remove", => @innerForm = null

        xhr.always -> target.prop "disabled", no

        this

    searchKeydown: (e) ->
        if (e.keyCode is 27)
            @$el.dropdown "toggle"
            return false

    applyConstraint: (reset = no) ->
        if @selector
            value = @model.get @constraint.field
            @selector.dataSource?.filters.set @constraint.otherField, value
            @selector.createAttributes[@constraint.otherField] = value

        @model.set(@key, if @multiple then [] else null) if reset

        this

    checkToDisable: ->
        (if _.isEmpty @model.get @constraint.field then @disable() else @enable()) if @constraint

        this


    disable: ->
        return this if @disableDropdown

        @disableDropdown = yes

        @toggleDisableControls()

    enable: ->
        return this if not @disableDropdown

        @disableDropdown = no

        @toggleDisableControls()

    toggleDisableControls: ->
        @dropdownBtn.prop "disabled", @disableDropdown
        @itemTitle.prop "disabled", @disableDropdown if not @multiple

        this

    renderDropdown: (e) ->
        if @disableDropdown
            e.preventDefault()

            return

        @opened = yes

        if not @selector
            @selector = new Cruddy.Inputs.EntitySelector
                model: @model
                key: @key
                multiple: @multiple
                reference: @reference
                allowCreate: @allowEdit
                owner: @owner

            @applyConstraint() if @constraint

            @$el.append @selector.render().el

        dataSource = @selector.dataSource

        dataSource.refresh() if not dataSource.inProgress()

        @toggleOpenDirection()

    toggleOpenDirection: ->
        return if not @opened

        wnd = $(window)
        space = wnd.height() - @$el.offset().top - wnd.scrollTop() - @$el.parent(".field-list").scrollTop()

        targetClass = if space > 292 then "open-down" else "open-up"

        @$el.removeClass("open-up open-down").addClass targetClass if not @$el.hasClass targetClass

        this

    applyChanges: (value) ->
        if @multiple
            @renderItems()
        else
            @updateItem()
            @$el.removeClass "open"

        @toggleOpenDirection()

        this

    render: ->
        @dispose()

        if @multiple then @renderMultiple() else @renderSingle()

        @dropdownBtn = @$ "##{ @cid }-dropdown"

        @$el.attr "id", @cid

        @checkToDisable()

        this

    renderMultiple: ->
        @$el.append @items = $ "<div>", class: "items"

        @$el.append """
            <button type="button" class="btn btn-default btn-block dropdown-toggle ed-dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }">
                #{ Cruddy.lang.choose }
                <span class="caret"></span>
            </button>
            """

        @renderItems()

    renderItems: ->
        html = ""
        html += @itemTemplate value.title, key for value, key in @getValue()
        @items.html html
        @items.toggleClass "has-items", html isnt ""

        this

    renderSingle: ->
        @$el.html @itemTemplate "", "0"

        @itemTitle = @$ ".form-control"
        @itemDelete = @$ ".btn-remove"
        @itemEdit = @$ ".btn-edit"

        @updateItem()

    updateItem: ->
        value = @getValue()
        @itemTitle.val if value then value.title else ""
        @itemDelete.toggle !!value
        @itemEdit.toggle !!value

        this

    itemTemplate: (value, key = null) ->
        html = """
        <div class="input-group input-group ed-item #{ if not @multiple then "ed-dropdown-toggle" else "" }" data-key="#{ key }">
            <input type="text" class="form-control" #{ if not @multiple then "data-toggle='dropdown' data-target='##{ @cid }' placeholder='#{ @placeholder }'" else "tab-index='-1'"} value="#{ _.escape value }" readonly>
            <div class="input-group-btn">
        """

        html += """
            <button type="button" class="btn btn-default btn-edit" tabindex="-1">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            """ if @allowEdit

        html += """
            <button type="button" class="btn btn-default btn-remove" tabindex="-1">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
            """

        if not @multiple
            html += """
                <button type="button" class="btn btn-default btn-dropdown dropdown-toggle" data-toggle="dropdown" id="#{ @cid }-dropdown" data-target="##{ @cid }" tab-index="1">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                """

        html += "</div></div>"

    dispose: ->
        @selector?.remove()
        @innerForm?.remove()

        this

    remove: ->
        @dispose()

        super
class Cruddy.Inputs.EntitySelector extends Cruddy.Inputs.Base
    className: "entity-selector"

    events:
        "click .item": "check"
        "click .more": "more"
        "click .btn-add": "add"
        "click [type=search]": -> false

    initialize: (options) ->
        super

        @filter = options.filter ? false
        @multiple = options.multiple ? false
        @reference = options.reference

        @allowSearch = options.allowSearch ? yes
        @allowCreate = options.allowCreate ? yes and @reference.createPermitted()

        @createAttributes = {}

        @data = []
        @buildSelected @model.get @key

        if @reference.viewPermitted()
            @primaryKey = "id"

            @dataSource = @reference.search ajaxOptions: data: owner: options.owner

            @listenTo @dataSource, "request", @loading
            @listenTo @dataSource, "data",    @renderItems
            @listenTo @dataSource, "error",   @displayError

        this

    checkForMore: ->
        @more() if @moreElement? and @items.parent().height() + 50 > @moreElement.position().top

        this

    check: (e) ->
        id = $(e.target).data("id").toString()
        @select _.find @dataSource.data, (item) -> item.id.toString() == id

        false

    select: (item) ->
        if @multiple
            if item.id of @selected
                value = _.filter @model.get(@key), (item) -> item.id != id
            else
                value = _.clone @model.get(@key)
                value.push item
        else
            value = item

        @setValue value

    more: ->
        return if not @dataSource or @dataSource.inProgress()

        @dataSource.next()

        false

    add: (e) ->
        e.preventDefault()
        e.stopPropagation()

        instance = @reference.createInstance attributes: @createAttributes

        console.log instance

        @innerForm = new Cruddy.Entity.Form
            model: instance
            inner: yes

        @innerForm.render().$el.appendTo document.body
        after_break => @innerForm.show()

        @listenToOnce @innerForm, "remove", => @innerForm = null

        @listenToOnce instance, "sync", (instance, resp) =>
            @select
                id: instance.id
                title: resp.data.title

            @dataSource.set "search", ""
            @innerForm.remove()

        this

    applyChanges: (data) ->
        @buildSelected data
        @renderItems()

    buildSelected: (data) ->
        @selected = {}

        if @multiple
            @selected[item.id] = yes for item in data
        else
            @selected[data.id] = yes if data?

        this

    loading: ->
        @moreElement?.addClass "loading"

        this

    renderItems: ->
        @moreElement = null

        html = ""

        if @dataSource.data.length or @dataSource.more
            html += @renderItem item for item in @dataSource.data

            html += """<li class="more #{ if @dataSource.inProgress() then "loading" else "" }">#{ Cruddy.lang.more }</li>""" if @dataSource.more
        else
            html += "<li class='empty'>#{ Cruddy.lang.no_results }</li>"

        @items.html html

        if @dataSource.more
            @moreElement = @items.children ".more"
            @checkForMore()

        this

    renderItem: (item) ->
        className = if item.id of @selected then "selected" else ""

        """<li class="item #{ className }" data-id="#{ item.id }">#{ item.title }</li>"""

    render: ->
        if @reference.viewPermitted()
            @dispose()

            @$el.html @template()

            @items = @$ ".items"

            @renderItems()

            @items.parent().on "scroll", $.proxy this, "checkForMore"

            @renderSearch() if @allowSearch
        else
            @$el.html "<span class=error>#{ Cruddy.lang.forbidden }</span>"

        this

    renderSearch: ->
        @searchInput = new Cruddy.Inputs.Search
            model: @dataSource
            key: "search"

        @$el.prepend @searchInput.render().el

        @searchInput.$el.wrap "<div class='#{ if @allowCreate then "input-group" else "" } search-input-container'></div>"

        @searchInput.$el.after """
            <div class='input-group-btn'>
                <button type='button' class='btn btn-default btn-add' tabindex='-1'>
                    <span class='glyphicon glyphicon-plus'></span>
                </button>
            </div>
            """ if @allowCreate

        this

    template: -> """<div class="items-container"><ul class="items"><li class="more loading"></li></ul></div>"""

    focus: ->
        @searchInput?.focus() or @entity.done => @searchInput.focus()

        this

    dispose: ->
        @searchInput?.remove()
        @innerForm?.remove()

        this

    remove: ->
        @dispose()

        super

class Cruddy.Inputs.FileList extends Cruddy.Inputs.Base
    className: "file-list"

    events:
        "change [type=file]": "appendFiles"
        "click .action-delete": "deleteFile"

    initialize: (options) ->
        @multiple = options.multiple ? false
        @formatter = options.formatter ? format: (value) -> if value instanceof File then value.name else value
        @accepts = options.accepts ? ""

        super

    deleteFile: (e) ->
        if @multiple
            value = _.clone @model.get @key
            value.splice $(e.currentTarget).data("index"), 1
        else
            value = ''

        @model.set @key, value

        false

    appendFiles: (e) ->
        return if e.target.files.length is 0

        if @multiple
            value = _.clone @model.get @key
            value.push file for file in e.target.files
        else
            value = e.target.files[0]

        @setValue value

    applyChanges: -> @render()

    render: ->
        value = @model.get @key
        html = ""

        if @multiple then html += @renderItem item, i for item, i in value else html += @renderItem value if value

        html = @wrapItems html if html

        html += @renderInput if @multiple then "<span class='glyphicon glyphicon-plus'></span> #{ Cruddy.lang.add }" else Cruddy.lang.choose

        @$el.html html

        this

    wrapItems: (html) -> """<ul class="list-group">#{ html }</ul>"""

    renderInput: (label) ->
        """
        <div class="btn btn-sm btn-default file-list-input-wrap">
            <input type="file" accept="#{ @accepts } "#{ "multiple" if @multiple }>
            #{ label }
        </div>
        """

    renderItem: (item, i = 0) ->
        label = @formatter.format item

        """
        <li class="list-group-item">
            <a href="#" class="action-delete pull-right" data-index="#{ i }"><span class="glyphicon glyphicon-remove"></span></a>

            #{ label }
        </li>
        """

class Cruddy.Inputs.ImageList extends Cruddy.Inputs.FileList
    className: "image-list"

    constructor: ->
        @readers = []

        super

    initialize: (options) ->
        @width = options.width ? 0
        @height = options.height ? 80

        super

    render: ->
        super

        reader.readAsDataURL reader.item for reader in @readers
        @readers = []

        @$(".fancybox").fancybox();

        this

    wrapItems: (html) -> """<ul class="image-group">#{ html }</ul>"""

    renderItem: (item, i = 0) ->
        """
        <li class="image-group-item">
            #{ @renderImage item, i }
            <a href="#" class="action-delete" data-index="#{ i }"><span class="glyphicon glyphicon-remove"></span></a>
        </li>
        """

    renderImage: (item, i = 0) ->
        id = @key + i

        if item instanceof File
            image = item.data or ""
            @readers.push @createPreviewLoader item, id if not item.data?
        else
            image = thumb item, @width, @height

        """
        <a href="#{ if item instanceof File then item.data or "#" else Cruddy.root + '/' + item }" class="fancybox">
            <img src="#{ image }" id="#{ id }">
        </a>
        """

    createPreviewLoader: (item, id) ->
        reader = new FileReader
        reader.item = item
        reader.onload = (e) ->
            e.target.item.data = e.target.result
            $("#" + id).attr("src", e.target.result).parent().attr "href", e.target.result

        reader
# Search input implements "change when type" and also allows to clear text with Esc
class Cruddy.Inputs.Search extends Cruddy.Inputs.Text

    attributes:
        type: "search"
        placeholder: Cruddy.lang.search

    scheduleChange: ->
        clearTimeout @timeout if @timeout?
        @timeout = setTimeout (=> @change()), 300

        this

    keydown: (e) ->

        # Backspace
        if e.keyCode is 8
            @model.set @key, ""
            return false

        @scheduleChange()

        super
class Cruddy.Inputs.Slug extends Backbone.View
    events:
        "click .btn": "toggleSyncing"

    constructor: (options) ->
        @input = new Cruddy.Inputs.Text _.clone options

        options.className ?= "input-group"

        delete options.attributes if options.attributes?

        super

    initialize: (options) ->
        chars = options.chars ? "a-z0-9\-_"

        @regexp = new RegExp "[^#{ chars }]+", "g"
        @separator = options.separator ? "-"

        @key = options.key
        @ref = if _.isArray(options.ref) then options.ref else [options.ref] if options.ref

        super

    toggleSyncing: ->
        if @syncButton.hasClass "active" then @unlink() else @link()

        this

    link: ->
        return if not @ref

        @listenTo @model, "change:" + @ref.join(" change:"), @sync
        @syncButton.addClass "active"
        @input.disable()

        @sync()

    unlink: ->
        @stopListening @model, null, @sync if @ref?
        @syncButton.removeClass "active"
        @input.enable()

        this

    linkable: ->
        modelValue = @model.get @key
        value = @getValue()

        value == modelValue or modelValue is null and value is ""

    convert: (value) -> if value then value.toLocaleLowerCase().replace(/\s+/g, @separator).replace(@regexp, "") else value

    sync: ->
        @model.set @key, @getValue()

        this

    getValue: ->
        components = []

        for key in @ref
            refValue = @model.get key
            components.push refValue if refValue

        if components.length then @convert components.join @separator else ""

    render: ->
        @$el.html @template()
        @$el.prepend @input.render().el

        if @ref?
            @syncButton = @$ ".btn"
            @link() if @linkable()

        this

    template: ->
        return "" if not @ref?

        """
        <div class="input-group-btn">
            <button type="button" tabindex="-1" class="btn btn-default" title="#{ Cruddy.lang.slug_sync }"><span class="glyphicon glyphicon-link"></span></button>
        </div>
        """
class Cruddy.Inputs.Select extends Cruddy.Inputs.Text
    tagName: "select"

    initialize: (options) ->
        @items = options.items ? {}
        @prompt = options.prompt ? null

        super

    applyChanges: (data, external) ->
        @$("[value='#{ data }']").prop "selected", yes if external

        this

    render: ->
        @$el.html @template()

        super

    template: ->
        html = ""
        html += @optionTemplate "", @prompt ? ""
        html += @optionTemplate key, value for key, value of @items
        html

    optionTemplate: (value, title) ->
        """<option value="#{ _.escape value }">#{ _.escape title }</option>"""
class Cruddy.Inputs.Code extends Cruddy.Inputs.Base
    initialize: (options) ->
        @$el.height (options.height ? 100) + "px"

        @editor = ace.edit @el
        @editor.setTheme "ace/theme/#{ options.theme ? Cruddy.ace_theme }"

        session = @editor.getSession()

        session.setMode "ace/mode/#{ options.mode }" if options.mode
        session.setUseWrapMode true
        session.setWrapLimitRange null, null

        super

    applyChanges: (value, external) ->
        if external
            @editor.setValue value
            @editor.getSession().getSelection().clearSelection()

        this

    render: ->
        @editor.on "blur", => @model.set @key, @editor.getValue(), input: @

        super

    remove: ->
        @editor?.destroy()
        @editor = null

        super

    focus: ->
        @editor?.focus()

        this
class Cruddy.Inputs.Markdown extends Cruddy.Inputs.Base

    events:
        "show.bs.tab [data-toggle=tab]": "showTab"
        "shown.bs.tab [data-toggle=tab]": "shownTab"

    initialize: (options) ->
        @height = options.height ? 200

        @editorInput = new Cruddy.Inputs.Code
            model: @model
            key: @key
            theme: options.theme
            mode: "markdown"
            height: @height

        super

    showTab: (e) ->
        @renderPreview() if $(e.target).data("tab") is "preview"

        this

    shownTab: (e) ->
        @editorInput.focus() if $(e.traget).data("tab") is "editor"

    render: ->
        @$el.html @template()

        @$(".tab-pane-editor").append @editorInput.render().el

        @preview = @$ ".tab-pane-preview"

        this

    renderPreview: ->
        @preview.html marked @getValue()

        this

    template: ->
        """
        <div class="markdown-editor">
            <a href="https://help.github.com/articles/github-flavored-markdown" target="_blank" class="hint">GitHub flavored markdown</a>

            <ul class="nav nav-tabs">
                <li class="active"><a href="##{ @cid }-editor" data-toggle="tab" data-tab="editor" tab-index="-1">#{ Cruddy.lang.markdown_source }</a></li>
                <li><a href="##{ @cid }-preview" data-toggle="tab" data-tab="preview" tab-index="-1">#{ Cruddy.lang.markdown_parsed }</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane-editor tab-pane active" id="#{ @cid }-editor"></div>
                <div class="tab-pane-preview tab-pane" id="#{ @cid }-preview" style="height:#{ @height }px"></div>
            </div>
        </div>
        """

    focus: ->
        tab = @$ "[data-tab=editor]"
        if tab.hasClass "active" then @editorInput.focus() else tab.tab "show"

        this
class Cruddy.Inputs.NumberFilter extends Cruddy.Inputs.Base
    className: "input-group number-filter"

    events:
        "click .dropdown-menu a": "changeOperator"
        "change": "changeValue"

    initialize: ->
        @defaultOp = "="

        @setValue @makeValue(@defaultOp, ""), silent: yes if not @getValue()

        super

    changeOperator: (e) ->
        e.preventDefault()

        op = $(e.currentTarget).data "op"
        value = @getValue()

        @setValue @makeValue op, value.val if value.op isnt op

        this

    changeValue: (e) ->
        value = @getValue()

        @setValue @makeValue value.op, e.target.value

        this

    applyChanges: (value, external) ->
        @$(".dropdown-menu li").removeClass "active"
        @$(".dropdown-menu a[data-op='#{ value.op }']").parent().addClass "active"

        @op.text value.op
        @input.val value.val if external

        this

    render: ->
        @$el.html @template()

        @op = @$component "op"
        @input = @$component "input"
        @reset = @$component "reset"

        super

    template: -> """
        <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span id="#{ @componentId("op") }" class="value">=</span>
                <span class="caret"></span>
            </button>

            <ul class="dropdown-menu">
                <li><a href="#" data-op="=">=</a></li>
                <li><a href="#" data-op="&gt;">&gt;</a></li>
                <li><a href="#" data-op="&lt;">&lt;</a></li>
            </ul>
        </div>

        <input type="text" class="form-control" id="#{ @componentId "input" }">
    """

    makeValue: (op, val) -> { op: op, val: val }
Cruddy.Fields = new Factory

class Cruddy.Fields.BaseView extends Backbone.View

    constructor: (options) ->
        @field = field = options.field

        @inputId = options.model.entity.id + "_" + field.id

        base = " field-"
        classes = [ field.getType(), field.id, @inputId ]
        className = "field" + base + classes.join base

        className += " required" if field.isRequired()
        className += " form-group"

        @className = if @className then className + " " + @className else className

        super

    initialize: (options) ->
        @listenTo @model, "sync",    @handleSync
        @listenTo @model, "request", @handleRequest
        @listenTo @model, "invalid", @handleInvalid

        this

    handleSync: -> @toggleVisibility()

    handleRequest: -> @hideError()

    handleInvalid: (model, errors) ->
        if @field.id of errors
            error = errors[@field.id]

            @showError if _.isArray error then _.first error else error

        this

    toggleVisibility: ->
        @$el.toggle @isVisible()

        this

    hideError: ->
        @error.hide()

        this

    showError: (message) ->
        @error.text(message).show()

        this

    focus: -> this

    render: ->
        @$(".field-help").tooltip
            container: "body"
            placement: "left"

        @error = @$ "##{ @cid }-error"

        this

    helpTemplate: ->
        help = @field.getHelp()
        if help then """<span class="glyphicon glyphicon-question-sign field-help" title="#{ _.escape help }"></span>""" else ""

    errorTemplate: -> """<span class="help-block error" id="#{ @cid }-error"></span>"""

    # Get whether the view is visible
    # The field is not visible when model is new and field is not editable or computed
    isVisible: -> @field.isEditable(@model.action()) or not @model.isNew()

    dispose: -> this

    remove: ->
        @dispose()

        super

# This is basic field view that will render in bootstrap's vertical form style.
class Cruddy.Fields.InputView extends Cruddy.Fields.BaseView
    handleRequest: (model) ->
        @isEditable = @field.isEditable(model.action())

        super

    handleSync: (model) ->
        @render() if @field.isEditable(model.action()) isnt @isEditable

        super

    hideError: ->
        @$el.removeClass "has-error"

        super

    showError: ->
        @$el.addClass "has-error"

        super

    # Render a field
    render: ->
        @dispose()

        @$el.html @template()

        @input = @field.createInput @model

        @$el.append @input.render().el

        @$el.append @errorTemplate()

        @toggleVisibility()

        @isEditable = @field.isEditable(@model.action())

        super

    label: (label) ->
        label ?= @field.getLabel()
        
        """
        <label for="#{ @inputId }" class="field-label">
            #{ @helpTemplate() }#{ _.escape label }
        </label>
        """

    # The default template that is shown when field is editable.
    template: -> @label()

    # Focus the input that this field view holds.
    focus: ->
        @input.focus()

        this

    dispose: ->
        @input?.remove()

        this

class Cruddy.Fields.Base extends Attribute
    viewConstructor: Cruddy.Fields.InputView

    # Create a view that will represent this field in field list
    createView: (model) -> new @viewConstructor { model: model, field: this }

    # Create an input that is used by default view
    createInput: (model) ->
        input = @createEditableInput model if @isEditable(model.action()) and model.isSaveable()

        input or new Cruddy.Inputs.Static { model: model, key: @id, formatter: this }

    # Create an input that is used when field is editable
    createEditableInput: (model) -> null

    # Create filter input that
    createFilterInput: (model) -> null

    # Get a label for filter input
    getFilterLabel: -> @attributes.label

    # Format value as static text
    format: (value) -> value or "n/a"

    # Get field's label
    getLabel: -> @attributes.label

    # Get whether the field is editable for specified action
    isEditable: (action) -> @attributes.fillable and @attributes.disabled isnt yes and @attributes.disabled isnt action

    # Get whether field is required
    isRequired: -> @attributes.required

    # Get whether the field is unique
    isUnique: -> @attributes.unique
class Cruddy.Fields.Input extends Cruddy.Fields.Base

    createEditableInput: (model) ->
        attributes = placeholder: @attributes.placeholder
        type = @attributes.input_type

        if type is "textarea"
            attributes.rows = @attributes.rows

            new Cruddy.Inputs.Textarea
                model: model
                key: @id
                attributes: attributes
        else
            attributes.type = type

            new Cruddy.Inputs.Text
                model: model
                key: @id
                mask: @attributes.mask
                attributes: attributes

    format: (value) -> if @attributes.input_type is "textarea" then "<pre>#{ super }</pre>" else super

class Cruddy.Fields.DateTime extends Cruddy.Fields.Input
    
    format: (value) -> if value is null then Cruddy.lang.never else moment.unix(value).calendar()
class Cruddy.Fields.Boolean extends Cruddy.Fields.Base
    
    createEditableInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id

    createFilterInput: (model) -> new Cruddy.Inputs.Boolean
        model: model
        key: @id
        tripleState: yes

    format: (value) -> if value then Cruddy.lang.yes else Cruddy.lang.no
class Cruddy.Fields.BaseRelation extends Cruddy.Fields.Base

    isVisible: -> @getReference().viewPermitted() and super

    # Get the referenced entity
    getReference: ->
        @reference = Cruddy.app.entity @attributes.reference if not @reference

        @reference

    getFilterLabel: -> @getReference().getSingularTitle()
class Cruddy.Fields.Relation extends Cruddy.Fields.BaseRelation

    createEditableInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        multiple: @attributes.multiple
        reference: @getReference()
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint

    createFilterInput: (model) -> new Cruddy.Inputs.EntityDropdown
        model: model
        key: @id
        reference: @getReference()
        allowEdit: no
        placeholder: Cruddy.lang.any_value
        owner: @entity.id + "." + @id
        constraint: @attributes.constraint

    format: (value) ->
        return Cruddy.lang.not_selected if _.isEmpty value
        
        if @attributes.multiple then _.pluck(value, "title").join ", " else value.title

    isEditable: -> super and @getReference().viewPermitted()

    canFilter: -> super and @getReference().viewPermitted()
class Cruddy.Fields.File extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.FileList
        model: model
        key: @id
        multiple: @attributes.multiple
        accepts: @attributes.accepts

    format: (value) -> if value instanceof File then value.name else value
class Cruddy.Fields.Image extends Cruddy.Fields.File

    createEditableInput: (model) -> new Cruddy.Inputs.ImageList
        model: model
        key: @id
        width: @attributes.width
        height: @attributes.height
        multiple: @attributes.multiple
        accepts: @attributes.accepts
class Cruddy.Fields.Slug extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Slug
        model: model
        key: @id
        chars: @attributes.chars
        ref: @attributes.ref
        separator: @attributes.separator
        
        attributes:
            placeholder: @attributes.placeholder
class Cruddy.Fields.Enum extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: @attributes.prompt
        items: @attributes.items

    createFilterInput: (model) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: Cruddy.lang.any_value
        items: @attributes.items

    format: (value) ->
        items = @attributes.items

        if value of items then items[value] else "n/a"
class Cruddy.Fields.Markdown extends Cruddy.Fields.Base

    createEditableInput: (model) -> new Cruddy.Inputs.Markdown
        model: model
        key: @id
        height: @attributes.height
        theme: @attributes.theme
class Cruddy.Fields.Code extends Cruddy.Fields.Base
    
    createEditableInput: (model) ->
        new Cruddy.Inputs.Code
            model: model
            key: @id
            height: @attributes.height
            mode: @attributes.mode
            theme: @attributes.theme
class Cruddy.Fields.EmbeddedView extends Cruddy.Fields.BaseView
    className: "has-many-view"

    events:
        "click .btn-create": "create"

    initialize: (options) ->
        @views = {}
        @collection = @model.get @field.id

        @listenTo @collection, "add", @add
        @listenTo @collection, "remove", @removeItem

        super

    handleInvalid: (model, errors) ->
        super if @field.id of errors and errors[@field.id].length

        this

    create: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.add @field.getReference().createInstance(), focus: yes

        this

    add: (model, collection, options) ->
        @views[model.cid] = view = new Cruddy.Fields.EmbeddedItemView
            model: model
            collection: @collection
            disabled: @field.isEditable()

        @body.append view.render().el

        after_break( -> view.focus()) if options?.focus

        @focusable = view if not @focusable

        @update()

        this

    removeItem: (model) ->
        if view = @views[model.cid]
            view.remove()
            delete @views[model.cid]

        @update()

        this

    render: ->
        @dispose()

        @$el.html @template()
        @body = @$ "##{ @cid }-body"
        @createButton = @$ ".btn-create"

        @add model for model in @collection.models

        @update()

        super

    update: ->
        @createButton.toggle @field.isMultiple() or @collection.isEmpty()

        this

    template: ->
        ref = @field.getReference()

        buttons = if ref.createPermitted() then b_btn("", "plus", ["default", "create"]) else ""

        """
        <div class='header field-label'>
            #{ @helpTemplate() }#{ _.escape @field.getLabel() } #{ buttons }
        </div>
        <div class="error-container has-error">#{ @errorTemplate() }</div>
        <div class='body' id='#{ @cid }-body'></div>
        """

    dispose: ->
        view.remove() for cid, view of @views
        @views = {}
        @focusable = null

        this

    remove: ->
        @dispose()

        super

    focus: ->
        @focusable?.focus()

        this

class Cruddy.Fields.EmbeddedItemView extends Backbone.View
    className: "has-many-item-view"

    events:
        "click .btn-delete": "deleteItem"

    initialize: (options) ->
        @collection = options.collection
        @disabled = options.disabled ? true

        super

    deleteItem: (e) ->
        e.preventDefault()
        e.stopPropagation()

        @collection.remove @model

        this

    render: ->
        @dispose()

        @$el.html @template()

        @fieldList = new FieldList
            model: @model
            disabled: @disabled or not @model.isSaveable()

        @$el.prepend @fieldList.render().el

        this

    template: -> if @model.entity.deletePermitted() or @model.isNew() then b_btn(Cruddy.lang.delete, "trash", ["default", "sm", "delete"]) else ""

    dispose: ->
        @fieldList?.remove()
        @fieldList = null

        this

    remove: ->
        @dispose()

        super

    focus: ->
        @fieldList?.focus()

        this

class Cruddy.Fields.RelatedCollection extends Backbone.Collection

    initialize: (items, options) ->
        @owner = options.owner
        @field = options.field

        # The flag is set when user has deleted some items
        @deleted = no

        @listenTo @owner, "sync", => @deleted = false

        super

    remove: ->
        @deleted = yes

        super

    hasChangedSinceSync: ->
        return yes if @deleted
        return yes for item in @models when item.hasChangedSinceSync()

        no

    copy: (copy) ->
        items = if @field.isUnique() then [] else (item.copy() for item in @models)

        new Cruddy.Fields.RelatedCollection items,
            owner: copy
            field: @field

    serialize: ->
        if @field.isMultiple() 
            data = {}

            data[item.cid] = item for item in @models

            data
        else
            @first()

class Cruddy.Fields.Embedded extends Cruddy.Fields.BaseRelation
    viewConstructor: Cruddy.Fields.EmbeddedView

    createInstance: (model, items) ->
        return items if items instanceof Backbone.Collection

        items = (if items or @isRequired() then [ items ] else []) if not @attributes.multiple

        ref = @getReference()
        items = (ref.createInstance item for item in items)

        new Cruddy.Fields.RelatedCollection items,
            owner: model
            field: this

    applyValues: (collection, items) ->
        items = [ items ] if not @attributes.multiple

        collection.set _.pluck(items, "attributes"), add: no

        # Add new items
        ref = @getReference()

        collection.add (ref.createInstance item for item in items when not collection.get item.id)

        this

    hasChangedSinceSync: (items) -> items.hasChangedSinceSync()

    copy: (copy, items) -> items.copy(copy)

    processErrors: (collection, errorsCollection) ->
        return if not _.isObject errorsCollection

        if not @attributes.multiple
            model = collection.first()
            model.trigger "invalid", model, errorsCollection if model

            return this

        for cid, errors of errorsCollection
            model = collection.get cid
            model.trigger "invalid", model, errors if model

        this

    triggerRelated: (event, collection, args) ->
        model.trigger.apply model, [ event, model ].concat(args) for model in collection.models

        this

    isMultiple: -> @attributes.multiple

class Cruddy.Fields.Number extends Cruddy.Fields.Base
    createEditableInput: (model) -> new Cruddy.Inputs.Text
        model: model
        key: @id
        attributes:
            type: "text"

    createFilterInput: (model) -> new Cruddy.Inputs.NumberFilter
        model: model
        key: @id
class Cruddy.Fields.Computed extends Cruddy.Fields.Base
    createInput: (model) -> new Cruddy.Inputs.Static { model: model, key: @id, formatter: this }

    isEditable: -> false
Cruddy.Columns = new Factory

class Cruddy.Columns.Base extends Attribute
    initialize: (attributes) ->
        @formatter = Cruddy.formatters.create attributes.formatter, attributes.formatterOptions if attributes.formatter?

        super

    # Return value's text representation
    format: (value) -> if @formatter? then @formatter.format value else _.escape value

    # Get column's header text
    getHeader: -> @attributes.header

    # Get column's class name
    getClass: -> "col-" + @id

    # Get whether a column can order items
    canOrder: -> @attributes.can_order
class Cruddy.Columns.Proxy extends Cruddy.Columns.Base
    initialize: (attributes) ->
        field = attributes.field ? attributes.id
        @field = attributes.entity.fields.get field

        @set "header", @field.get "label" if attributes.header is null

        super

    format: (value) -> if @formatter? then @formatter.format value else @field.format value

    getClass: -> super + " col-" + @field.get "type"
class Cruddy.Columns.Computed extends Cruddy.Columns.Base
    getClass: -> super + " col-computed"
Cruddy.formatters = new Factory

class BaseFormatter
    defaultOptions: {}

    constructor: (options = {}) ->
        @options = $.extend {}, @defaultOptions, options

        this

    format: (value) -> value
class Cruddy.formatters.Image extends BaseFormatter
    defaultOptions:
        width: 40
        height: 40

    format: (value) ->
        return "" if _.isEmpty value
        value = value[0] if _.isArray value

        """
        <img src="#{ thumb value, @options.width, @options.height }" width="#{ @options.width or @defaultOptions.width }" height="#{ @options.height or @defaultOptions.height }" alt="#{ _.escape value }">
        """
class Cruddy.formatters.Plain extends BaseFormatter
    format: (value) -> _.escape value
Cruddy.Entity = {}

class Cruddy.Entity.Entity extends Backbone.Model

    initialize: (attributes, options) ->
        @fields = @createCollection Cruddy.Fields, attributes.fields
        @columns = @createCollection Cruddy.Columns, attributes.columns

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
        data = { order_by: @get("order_by") }
        data.order_dir = if data.order_dir? then @columns.get(data.order_by).get "order_dir" else "asc"

        new DataSource data, { entity: this, columns: columns, filter: new Backbone.Model }

    # Create filters for specified columns
    createFilters: (columns = @columns) ->
        filters = (col.createFilter() for col in columns.models when col.get("filter_type") is "complex")

        new Backbone.Collection filters

    # Create an instance for this entity
    createInstance: (attributes = {}, options = {}) ->
        attributes = _.extend {}, @get("defaults"), attributes.attributes
        options.entity = this

        new Cruddy.Entity.Instance attributes, options

    # Get relation field
    getRelation: (id) ->
        field = @fields.get id

        if not field
            console.error "The field #{id} is not found."

            return

        if not field instanceof Cruddy.Fields.BaseRelation
            console.error "The field #{id} is not a relation."

            return

        field

    search: (options = {}) -> new SearchDataSource {}, $.extend { url: @url() }, options

    # Load a model
    load: (id) ->
        xhr = $.ajax
            url: @url(id)
            type: "GET"
            dataType: "json"
            cache: yes
            displayLoading: yes

        xhr.then (resp) =>
            resp = resp.data

            @createInstance resp

    # Load a model and set it as current
    actionUpdate: (id) -> @load(id).then (instance) =>
            @set "instance", instance

            instance

    # Create new model and set it as current
    actionCreate: -> @set "instance", @createInstance()

    # Get only those attributes are not unique for the model
    getCopyableAttributes: (model, attributes) ->
        data = {}
        data[field.id] = attributes[field.id] for field in @fields.models when not field.isUnique() and field.id of attributes and not _.contains(@attributes.related, field.id)

        for ref in @attributes.related when ref of attributes
            data[ref] = @getRelation(ref).copy model, attributes[ref]

        data

    # Get url that handles syncing
    url: (id) -> entity_url @id, id

    # Get link to this entity or to the item of the entity
    link: (id) -> "#{ @id}" + if id? then "/#{ id }" else ""

    # Get title in plural form
    getPluralTitle: -> @attributes.title.plural

    # Get title in singular form
    getSingularTitle: -> @attributes.title.singular

    getPermissions: -> @attributes.permissions

    updatePermitted: -> @attributes.permissions.update

    createPermitted: -> @attributes.permissions.create

    deletePermitted: -> @attributes.permissions.delete

    viewPermitted: -> @attributes.permissions.view

    isSoftDeleting: -> @attributes.soft_deleting
class Cruddy.Entity.Instance extends Backbone.Model
    constructor: (attributes, options) ->
        @entity = options.entity
        @related = {}

        super
        
    initialize: (attributes, options) ->
        @original = _.clone attributes

        @on "error", @processError, this
        @on "sync", @handleSync, this
        @on "destroy", => @set "deleted_at", moment().unix() if @entity.get "soft_deleting"

        @on event, @triggerRelated(event), this for event in ["sync", "request"]

        this

    handleSync: ->
        @original = _.clone @attributes

        this

    # Get a function handler that passes events to the related models
    triggerRelated: (event) -> 
        slice = Array.prototype.slice

        (model) ->
            for id, related of @related
                relation = @entity.getRelation id
                relation.triggerRelated.call relation, event, related, slice.call arguments, 1

            this

    processError: (model, xhr) ->
        if xhr.responseJSON?.error is "VALIDATION"
            errors = xhr.responseJSON.data

            @trigger "invalid", this, errors

            # Trigger errors for related models
            @entity.getRelation(id).processErrors model, errors[id] for id, model of @related when id of errors

    validate: ->
        @set "errors", {}
        null

    link: -> @entity.link if @isNew() then "create" else @id

    url: -> @entity.url @id

    set: (key, val, options) ->
        if typeof key is "object"
            attrs = key
            options = val
            is_copy = options?.is_copy

            for id in @entity.get "related" when id of attrs
                relation = @entity.getRelation id
                relationAttrs = attrs[id]

                if is_copy
                    related = @related[id] = relationAttrs

                else if id of @related
                    related = @related[id]
                    relation.applyValues related, relationAttrs if relationAttrs

                else
                    related = @related[id] = relation.createInstance this, relationAttrs
                    related.parent = this

                # Attribute will now hold instance
                attrs[id] = related

        super

    sync: (method, model, options) ->
        if method in ["update", "create"]
            # Form data will allow us to upload files via AJAX request
            options.data = new AdvFormData(options.attrs ? @attributes).original

            # Set the content type to false to let browser handle it
            options.contentType = false
            options.processData = false

        super

    parse: (resp) -> resp.data.attributes

    copy: ->
        copy = @entity.createInstance()

        copy.set @getCopyableAttributes(copy),
            silent: yes
            is_copy: yes

        copy

    getCopyableAttributes: (copy) -> @entity.getCopyableAttributes copy, @attributes

    hasChangedSinceSync: ->
        return yes for key, value of @attributes when if key of @related then @entity.getRelation(key).hasChangedSinceSync value else not _.isEqual value, @original[key]

        no

    # Get whether is allowed to save instance
    isSaveable: -> (@isNew() and @entity.createPermitted()) or (!@isNew() and @entity.updatePermitted())

    serialize: -> { attributes: @attributes, id: @id }

    # Get current action on the model
    action: -> if @isNew() then "create" else "update"
class Cruddy.Entity.Page extends Cruddy.View
    className: "page entity-page"

    events: {
        "click .btn-create": "create"
        "click .btn-refresh": "refresh"
    }

    constructor: (options) ->
        @className += " entity-page-" + options.model.id

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

            @form = new Cruddy.Entity.Form model: instance
            @$el.append @form.render().$el

            after_break => @form.show()

        this

    create: ->
        Cruddy.router.navigate @model.link("create"), trigger: true

        this

    refresh: (e) ->
        btn = $ e.currentTarget
        btn.prop "disabled", yes

        @dataSource.fetch().always -> btn.prop "disabled", no

        this

    render: ->
        @dispose()

        @$el.html @template()

        @dataSource = @model.createDataSource()
        
        @dataSource.fetch()

        # Search input
        @search = @createSearchInput @dataSource

        @$component("search").append @search.render().el

        # Filters
        if not _.isEmpty filters = @dataSource.entity.get "filters"
            @filterList = @createFilterList @dataSource.filter, filters

            @$component("filters").append @filterList.render().el

        # Data grid
        @dataGrid = @createDataGrid @dataSource
        @pagination = @createPagination @dataSource
        
        @$component("body").append(@dataGrid.render().el).append(@pagination.render().el)

        this

    createDataGrid: (dataSource) -> new DataGrid
        model: dataSource
        entity: @model

    createPagination: (dataSource) -> new Pagination model: dataSource

    createFilterList: (model, filters) -> new FilterList
        model: model
        entity: @model
        filters: filters

    createSearchInput: (dataSource) -> new Cruddy.Inputs.Search
        model: dataSource
        key: "search"

    template: ->
        html = """
            <div class="content-header">
                <div class="column column-main">
                    <h1 class="entity-title">#{ @model.getPluralTitle() }</h1>

                    <div class="entity-title-buttons">
                        #{ @buttonsTemplate() }
                    </div>
                </div>

                <div class="column column-extra">
                    <div class="entity-search-box" id="#{ @componentId "search" }"></div>
                </div>
            </div>
            
            <div class="content-body">
                <div class="column column-main" id="#{ @componentId "body" }"></div>
                <div class="column column-extra" id="#{ @componentId "filters" }"></div>
            </div>
        """

    buttonsTemplate: ->
        html = """<button type="button" class="btn btn-default btn-refresh" title="#{ Cruddy.lang.refresh }">#{ b_icon "refresh" }</button>"""
        html += """ <button type="button" class="btn btn-primary btn-create" title="#{ Cruddy.lang.add }">#{ b_icon "plus" }</button>""" if @model.createPermitted()

        html

    dispose: ->
        @form?.remove()
        @filterList?.remove()
        @dataGrid?.remove()
        @pagination?.remove()
        @search?.remove()
        @dataSource?.stopListening()

        this

    remove: ->
        @dispose()

        super
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
        @listenTo @model, "change",  @handleChange

        @listenTo model, "change",  @handleChange for key, model of @model.related

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

    handleChange: -> 
        # @$el.toggleClass "dirty", @model.hasChangedSinceSync()

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

    displayInvalid: -> @displayAlert Cruddy.lang.invalid, "warning", 5000

    displayError: (xhr) -> @displayAlert Cruddy.lang.failure, "danger", 5000 unless xhr.responseJSON?.error is "VALIDATION"

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

        @request = @model.save null,
            displayLoading: yes

            xhr: =>
                xhr = $.ajaxSettings.xhr()
                xhr.upload.addEventListener('progress', $.proxy @, "progressCallback") if xhr.upload

                xhr

        @request.done($.proxy this, "displaySuccess").fail($.proxy this, "displayError")

        @request.always =>
            @request = null
            @progressBar.parent().hide()
            @update()

        @update()

        this

    progressCallback: (e) ->
        if e.lengthComputable
            width = (e.loaded * 100) / e.total

            @progressBar.width(width + '%').parent().show()

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
        @progressBar = @$ ".form-save-progress"

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
            <div class="container-fluid">
                <button type="button" class="btn btn-link btn-destroy navbar-btn pull-right" type="button"></button>
                
                <button type="button" tabindex="-1" class="btn btn-link btn-copy navbar-btn pull-right" title="#{ Cruddy.lang.copy }">
                    <span class="glyphicon glyphicon-book"></span>
                </button>
                
                <ul class="nav navbar-nav"></ul>
            </div>
        </div>

        <footer>
            <button type="button" class="btn btn-default btn-close" type="button">#{ Cruddy.lang.close }</button>
            <button type="button" class="btn btn-primary btn-save" type="button" disabled></button>

            <div class="progress"><div class="progress-bar form-save-progress"></div></div>
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
# Backend application file

class App extends Backbone.Model
    initialize: ->
        @container = $ "body"
        @mainContent = $ "#content"
        @loadingRequests = 0
        @entities = {}
        @entitiesDfd = {}

        # Create entities
        @entities[entity.id] = new Cruddy.Entity.Entity entity for entity in Cruddy.entities

        @on "change:entity", @displayEntity, this

        this

    displayEntity: (model, entity) ->
        @dispose()

        @mainContent.hide()
        @container.append (@page = new Cruddy.Entity.Page model: entity).render().el if entity

    displayError: (error) ->
        @dispose()
        @mainContent.html("<p class='alert alert-danger'>#{ error }</p>").show()

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

    entity: (id) ->
        console.error "Unknown entity #{ id }" if not id of @entities

        @entities[id]

    dispose: ->
        @page?.remove()

        this

Cruddy.app = new App

class Router extends Backbone.Router

    initialize: ->
        entities = (_.map Cruddy.entities, (entity) -> entity.id).join "|"

        @addRoute "index", entities
        @addRoute "update", entities, "([^/]+)"
        @addRoute "create", entities, "create"

        root = Cruddy.root + "/" + Cruddy.uri + "/"
        history = Backbone.history
        hashStripper = /#.*$/

        $(document.body).on "click", "a", (e) ->
            fragment = e.currentTarget.href.replace hashStripper, ""
            oldFragment = history.fragment

            if fragment.indexOf(root) is 0 and (fragment = fragment.slice root.length) and fragment isnt oldFragment
                loaded = history.loadUrl fragment

                # Backbone will set fragment even if no route matched so we need to
                # restore old fragment
                history.fragment = oldFragment

                if loaded
                    e.preventDefault()
                    history.navigate fragment

            e

        this

    addRoute: (name, entities, appendage = null) ->
        route = "^(#{ entities })"
        route += "/" + appendage if appendage
        route += "$"

        @route new RegExp(route), name

        this

    resolveEntity: (id) ->
        entity = Cruddy.app.entity(id)

        if entity.viewPermitted()
            entity.set "instance", null
            Cruddy.app.set "entity", entity

            entity
        else
            Cruddy.app.displayError Cruddy.lang.entity_forbidden

            null

    index: (entity) -> @resolveEntity entity

    create: (entity) ->
        console.log 'create'

        entity = @resolveEntity entity
        entity.actionCreate() if entity

        entity

    update: (entity, id) ->
        entity = @resolveEntity entity

        entity.actionUpdate id if entity

        entity

Cruddy.router = new Router

Backbone.history.start
    root: Cruddy.uri
    pushState: true
    hashChange: false