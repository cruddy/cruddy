<?php

namespace Kalnoy\Cruddy\Schema\Fields;

/**
 * @method Types\Primary    increments(string $id)
 * @method Types\StringField string(string $id)
 * @method Types\Text       text(string $id)
 * @method Types\Email      email(string $id)
 * @method Types\Password   password(string $id)
 * @method Types\DateTime   datetime(string $id)
 * @method Types\Time       time(string $id)
 * @method Types\Date       date(string $id)
 * @method Types\Boolean    boolean(string $id)
 * @method Types\Boolean    bool(string $id)
 * @method Types\File       file(string $id)
 * @method Types\Image      image(string $id)
 * @method Types\Integer    integer(string $id)
 * @method Types\FloatField float(string $id)
 * @method void             timestamps(bool $hide = false, bool $disableCreatedAt = null)
 * @method BasicRelation    relates(string $id, string $entityId = null)
 * @method InlineRelation   embed(string $id, string $entityId = null)
 * @method Types\Slug       slug(string $id, string $refFieldId = null)
 * @method Types\Enum       enum(string $id, mixed $items)
 * @method Types\Computed   computed(string $id, mixed $accessor = null)
 * @method Types\Computed   compute(string $id, mixed $accessor = null)
 *
 * @method \Kalnoy\Cruddy\CKEditor\CKEditor ckedit(string $id)
 * @method \Kalnoy\Cruddy\Ace\Markdown markdown(string $id)
 * @method \Kalnoy\Cruddy\Ace\Code code(string $id)
 */
class InstanceFactory extends \Kalnoy\Cruddy\Schema\InstanceFactory {

}