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

        this

    wrapItems: (html) -> """<ul class="image-group">#{ html }</ul>"""

    renderItem: (item) ->
        """
        <li class="image-group-item">
            #{ @renderImage item }
            <a href="#" class="action-delete" data-cid="#{ @itemId(item) }"><span class="glyphicon glyphicon-remove"></span></a>
        </li>
        """

    renderImage: (item) ->
        if isFile = item instanceof File
            image = item.data or ""
            @readers.push @createPreviewLoader item if not item.data?
        else
            image = thumb item, @width, @height

        """
        <a href="#{ if isFile then item.data or "#" else Cruddy.root + '/' + item }" class="img-wrap" data-trigger="fancybox">
            <img src="#{ image }" #{ if isFile then "id='"+item.cid+"'" else "" }>
        </a>
        """

    createPreviewLoader: (item) ->
        reader = new FileReader
        reader.item = item
        reader.onload = (e) ->
            e.target.item.data = e.target.result
            $("#" + item.cid).attr("src", e.target.result).parent().attr "href", e.target.result

        reader