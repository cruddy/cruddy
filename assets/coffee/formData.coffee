class AdvFormData
    constructor: (data) ->
        @original = new FormData
        @append data if data?

    append: (value, name) ->
        return if value is undefined

        return @original.append name, value if value instanceof File or value instanceof Blob

        if _.isObject(value) or _.isArray(value)
            return @original.append name, "" if _.isEmpty(value)

            _.each value, (value, key) => @append value, @key(name, key)

            return

        @original.append name, @process value

    process: (value) ->
        return "" if value is null
        return 1 if value is yes
        return 0 if value is no

        value

    key: (outer, inner) -> if outer then "#{ outer }[#{ inner }]" else inner