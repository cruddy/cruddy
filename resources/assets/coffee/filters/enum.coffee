class Cruddy.Filters.Enum extends Cruddy.Filters.Base
    createFilterInput: (model) -> new Cruddy.Inputs.Select
        model: model
        key: @id
        prompt: Cruddy.lang.any_value
        items: @attributes.items
        multiple: yes

    parseData: (value) -> if _.isString value then value.split "," else null