class Cruddy.Fields.Image.Formatter

    constructor: (options) ->
        @options = { width: options.width || 0, height: options.height || 0 }
        @storage = Cruddy.FileStorage.instance options.storage

        return

    imageUrl: (image) -> @storage.url image

    imageThumb: (image) -> @storage.url image, @options

    format: (value) ->
        html = """<ul class="image-group">"""

        value = [ value ] if not _.isArray value

        for image in value
            html += """
                <li class="image-group-item">
                    <a href="#{ @imageUrl image }" class="img-wrap" target="_blank" data-trigger="fancybox">
                        <img src="#{ @imageThumb image }">
                    </a>
                </li>
            """

        return html + "</ul>"