class Cruddy.Inputs.ImageList extends Cruddy.Inputs.FileList
    className: "file-list --images"

    initialize: (options) ->
        @width = options.width ? 0
        @height = options.height ? 80

        super

    wrapItems: (html) -> """<ul class="image-group">#{ html }</ul>"""

    renderFile: (file) -> """
        <li class="image-group-item">
            #{ @renderImage file }

            <a href="#" class="action-delete" data-cid="#{ file }">
                <span class="glyphicon glyphicon-remove"></span>
            </a>
        </li>
        """

    renderImage: (file) -> """
        <a href="#{ @storage.url file }" target="_blank" class="img-wrap" data-trigger="fancybox">
            <img src="#{ @storage.url file, { width: @width, height: @height } }">
        </a>
    """

    getAccept: -> "image/*,image/jpeg,image/png,image/gif,image/jpeg"