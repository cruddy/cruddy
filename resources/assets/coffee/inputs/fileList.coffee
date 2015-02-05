class Cruddy.Inputs.FileList extends Cruddy.Inputs.Base
    className: "file-list"

    events:
        "change [type=file]": "appendFiles"
        "click .action-delete": "deleteFile"

    initialize: (options) ->
        @multiple = options.multiple ? false
        @formatter = options.formatter ? format: (value) -> if value instanceof File then value.name else value
        @accepts = options.accepts ? ""
        @counter = 1

        super

    deleteFile: (e) ->
        if @multiple
            cid = $(e.currentTarget).data("cid")
            @setValue _.reject @getValue(), (item) => @itemId(item) is cid
        else
            @setValue null

        false

    appendFiles: (e) ->
        return if e.target.files.length is 0

        file.cid = @cid + "_" + @counter++ for file in e.target.files

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

        if value
            html += @renderItem item for item in if @multiple then value else [ value ]

        html = @wrapItems html if html.length

        html += @renderInput if @multiple then "<span class='glyphicon glyphicon-plus'></span> #{ Cruddy.lang.add }" else Cruddy.lang.choose

        @$el.html html

        this

    wrapItems: (html) -> """<ul class="list-group">#{ html }</ul>"""

    renderInput: (label) ->
        """
        <div class="btn btn-sm btn-default file-list-input-wrap">
            <input type="file" id="#{ @componentId "input" }" accept="#{ @accepts }"#{ if @multiple then " multiple" else "" }>
            #{ label }
        </div>
        """

    renderItem: (item) ->
        label = @formatter.format item

        """
        <li class="list-group-item">
            <a href="#" class="action-delete pull-right" data-cid="#{ @itemId(item) }"><span class="glyphicon glyphicon-remove"></span></a>

            #{ label }
        </li>
        """

    itemId: (item) -> if item instanceof File then item.cid else item

    focus: ->
        @$component("input")[0].focus()

        this

