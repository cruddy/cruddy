class Cruddy.formatters.Image extends BaseFormatter
    defaultOptions:
        width: 40
        height: 40

    format: (value) ->
        return "" if _.isEmpty value
        value = value[0] if _.isArray value
        value = value.title if _.isObject value

        """
        <a href="#{ Cruddy.root + "/" + value }" data-trigger="fancybox">
            <img src="#{ thumb value, @options.width, @options.height }" #{ if @options.width then " width=#{ @options.width }" else "" } #{ if @options.height then " height=#{ @options.height }" else "" } alt="#{ _.escape value }">
        </a>
        """