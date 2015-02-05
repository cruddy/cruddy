class Cruddy.Filters.Base extends Cruddy.Attribute

    getLabel: -> @attributes.label

    getClass: -> "filter filter__" + @attributes.type + " filter--" + @id

    createFilterInput: -> throw "Implement required"

    prepareData: (value) -> value

    parseData: (value) -> value

    getDataKey: -> @get("data_key") or @id