class ImageList extends FileList


    constructor: ->
        @className += " image-list"
        @readers = []

        super

    initialize: (options) ->
        @width = options.width ? 40
        @height = options.height ? 40

        super

    render: ->
        super

        reader.readAsDataURL reader.item for reader in @readers
        @readers = []

        @$(".fancybox").fancybox();

        this

    renderItem: (item, i = 0) ->
        label = @formatter.format item

        """
        <li class="list-group-item">
            #{ @renderImage item, i }
            <a href="#" class="action-delete pull-right" data-index="#{ i }"><span class="glyphicon glyphicon-remove"></span></a>

            #{ label }
        </li>
        """

    renderImage: (item, i = 0) ->
        id = @key + i

        if item instanceof File
            image = if item.data then "background-image:url(#{ item.data }" else ""
            @readers.push @createPreviewLoader item, id if not item.data?
        else
            image = "background-image:url('#{ item }')"

        """
        <a href="#{ if item instanceof File then item.data or "#" else item }" class="fancybox">
            <span class="image-thumbnail" id="#{ id }" style="width:#{ @width }px;height:#{ @height }px;#{ image }"></span>
        </a>
        """

    createPreviewLoader: (item, id) ->
        reader = new FileReader
        reader.item = item
        reader.onload = (e) ->
            e.target.item.data = e.target.result
            $("#" + id).css("background-image", "url(#{ e.target.result })").parent().attr "href", e.target.result

        reader