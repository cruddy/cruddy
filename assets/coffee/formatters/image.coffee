class Cruddy.formatters.Image extends BaseFormatter
    defaultOptions:
        width: 40
        height: 40

    format: (value) ->
        return "" if _.isEmpty value
        value = value[0] if _.isArray value

        """
        <span class="image-thumbnail" style="width:#{ @options.width }px;height:#{ @options.height }px;background-image:url(#{ value });"></span>
        """