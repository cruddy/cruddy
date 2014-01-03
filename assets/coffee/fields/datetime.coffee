# DATE AND TIME FIELD TYPE

###
class Cruddy.fields.DateTimeView extends Cruddy.fields.InputView
    format: (value) -> moment.unix(value).format @field.get "format"
    unformat: (value) -> moment(value, @field.get "format").unix()
###

class Cruddy.fields.DateTime extends Cruddy.fields.Input
    #viewConstructor: Cruddy.fields.DateTimeView

    format: (value) -> if value is null then "никогда" else moment.unix(value).calendar()