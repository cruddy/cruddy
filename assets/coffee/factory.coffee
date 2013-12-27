class Factory
    types: {}

    register: (name, constructor) -> @types[name] = constructor

    create: (name, options) ->
        constructor = @types[name]
        new constructor options if constructor?