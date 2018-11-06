<?php

namespace LaravelORM;

use LaravelORM\Schema;

use LaravelORM\Fields\{
    Field, IncrementField, StringField, CreatedField
};
use LaravelORM\CompositeFields\BelongsField;
use LaravelORM\CompositeFields\MorphField;

use LaravelORM\Interfaces\IsACallableField;
use App\User;

class Options
{
    const TABLE = 0;
    const INCREMENTS = 1;
    const TIMESTAMPS = 2;
}
//
// class BelongsToField extends IntegerField
// {
//     public function setName($name) {
//         $this->name = $name.'_id';
//
//         return $this;
//     }
// }
//
//
//
// class CreatedField extends TimestampField
// {
//     protected $fillable = false;
//     // protected $default = DB::raw('CURRENT_TIMESTAMP');
// }

class Model extends User
{
    protected static $schema;

    public function __construct() {
        $schema = self::getSchema();

        $this->fillable = $schema->getFillableFields();
        $this->visible = $schema->getVisibleFields();
    }

//    abstract protected static function __fields($fields, $field);

    // TODO: Gérer le camel/snake case pour group_id ou groupId
    protected static function __schema($schema, $fields)
    {
        // Créer des fields !
        $fields->id = IncrementField::class;
        $fields->name = StringField::class;
        $fields->group = BelongsField::to(User::class);
        //$schema->fields->owner = MorphField::class;
        // $schema->fields->friends = ManyToManyField::class;
        // $schema->fields->friends = ManyToMorphField::class;
        // $schema->fields->owner = MorphField::only(User::class);
        $schema->timestamps();

        $schema->unique($fields->group, $fields->name);
    }

    protected static function generateSchema() {
        if (!self::$schema) {
            self::$schema = new Schema(self::class);

            self::__schema(self::$schema, self::$schema->fields);

            self::$schema->lock();
        }

        return self::$schema;
    }

    public static function getSchema()
    {
        return self::generateSchema();
    }

    public function __call($name, $args) {
        if (self::hasFieldOrFake($name)) {
            return self::getFieldOrFake($name)->call($this, ...$args);
        } else {
            return parent::__call($name, $args);
        }
    }

    public function __get($name) {
        if (self::hasFieldOrFake($name)) {
            return self::getFieldOrFake($name)->get($this);
        } else {
            return parent::__get($name);
        }
    }
}
