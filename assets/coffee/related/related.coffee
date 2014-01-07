Cruddy.related = new Factory

class Related extends Backbone.Model
    resolve: ->
        return @resolver if @resolver?

        @resolver = Cruddy.app.entity @get "related"
        @resolver.done (entity) => @related = entity

class Cruddy.related.One extends Related
    associate: (parent, child) ->
        child.set @get("foreign_key"), parent.id

        this

class Cruddy.related.MorphOne extends Cruddy.related.One
    associate: (parent, child) ->
        child.set @get("morph_type"), @get("morph_class")

        super