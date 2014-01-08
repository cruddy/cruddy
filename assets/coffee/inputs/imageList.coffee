class ImageList extends FileList
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
        <a href="#{ if item instanceof File then item.data or "#" else item }" class="fancybox">
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