
class Cruddy.Fields.DateTime extends Cruddy.Fields.Input
    
    format: (value) -> if value is null then Cruddy.lang.never else moment.unix(value).calendar()