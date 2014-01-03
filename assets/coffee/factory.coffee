class Factory
    create: (name, options) ->
        constructor = @[name]
        new constructor options if constructor?