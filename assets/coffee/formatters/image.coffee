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