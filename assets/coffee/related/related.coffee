Cruddy.Related = new Factory

class Cruddy.Related.Base extends Backbone.Model
    resolve: ->
        return @resolver if @resolver?

        @resolver = Cruddy.app.entity @get "related"
        @resolver.done (entity) => @related = entity

class Cruddy.Related.One extends Cruddy.Related.Base
    associate: (parent, child) ->
        child.set @get("foreign_key"), parent.id

        this

class Cruddy.Related.MorphOne extends Cruddy.Related.One
    associate: (parent, child) ->
        child.set @get("morph_type"), @get("morph_class")

        super