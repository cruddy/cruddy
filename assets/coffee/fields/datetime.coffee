
class Cruddy.Fields.DateTime extends Cruddy.Fields.Input
    
    format: (value) -> if value is null then "никогда" else moment.unix(value).calendar()