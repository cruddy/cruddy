# DATE AND TIME FIELD TYPE

###
class Cruddy.Fields.DateTimeView extends Cruddy.Fields.InputView
    format: (value) -> moment.unix(value).format @field.get "format"
    unformat: (value) -> moment(value, @field.get "format").unix()
###

class Cruddy.Fields.DateTime extends Cruddy.Fields.Input
    #viewConstructor: Cruddy.Fields.DateTimeView

    format: (value) -> if value is null then "никогда" else moment.unix(value).calendar()