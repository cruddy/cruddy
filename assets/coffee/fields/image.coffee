class Cruddy.Fields.Image extends Cruddy.Fields.File

    createEditableInput: (model) -> new Cruddy.Inputs.ImageList
        model: model
        key: @id
        width: @attributes.width
        height: @attributes.height
        multiple: @attributes.multiple
        accepts: @attributes.accepts

    createStaticInput: (model) -> new Cruddy.Inputs.Static
        model: model
        key: @id
        formatter: new Cruddy.Fields.Image.Formatter
            width: @attributes.width
            height: @attributes.height

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