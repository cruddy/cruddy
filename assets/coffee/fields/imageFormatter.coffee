class Cruddy.Fields.Image.Formatter

    constructor: (options) ->
        @options = options

        return

    imageUrl: (image) -> Cruddy.root + "/" + image

    imageThumb: (image) -> thumb image, @options.width, @options.height

    format: (value) ->
        html = """<ul class="image-group">"""

        value = [ value ] if not _.isArray value

        for image in value
            html += """
                <li class="image-group-item">
                    <a href="#{ @imageUrl image }" class="img-wrap" data-trigger="fancybox">
                        <img src="#{ @imageThumb image }">
                    </a>
                </li>
            """

        return html + "</ul>"