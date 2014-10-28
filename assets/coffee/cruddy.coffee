$.extend Cruddy,

    Fields: new Factory
    Columns: new Factory
    formatters: new Factory

    getHistoryRoot: -> @baseUrl.substr @root.length