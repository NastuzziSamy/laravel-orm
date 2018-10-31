<?php

namespace NastuzziSamy\Laravel;

use NastuzziSamy\Laravel\Schema;

use NastuzziSamy\Laravel\Fields\{
    Field, IncrementField, StringField, CreatedField, BelongsToField
};

use NastuzziSamy\Laravel\Interfaces\IsACallableField;
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
    }

//    abstract protected static function __fields($fields, $field);

    // TODO: Gérer le camel/snake case pour group_id ou groupId
    protected static function __schema($schema)
    {
        // Créer des fields !
        $schema->fields->id = new IncrementField;
        $schema->fields->name = new StringField;
        $schema->fields->group = (new BelongsToField)->to(User::class)->name('custom');
        $schema->timestamps();

        $schema->unique($schema->group, $schema->name);
    }

    protected static function generateSchema() {
        if (!self::$schema) {
            self::$schema = new Schema(self::class);

            self::__schema(self::$schema);

            self::$schema->lock();
        }

        return self::$schema;
    }

    public static function getSchema()
    {
        return self::generateSchema();
    }

    public static function hasField($name)
    {
        return self::getSchema()->hasField($name);
    }

    public static function getField($name)
    {
        return self::getSchema()->getField($name);
    }

    public static function getFields()
    {
        return self::getSchema()->getFields();
    }

    public function __call($name, $args) {
        if (self::hasField($name) && (($field = self::getField($name)) instanceof IsACallableField)) {
            return $field->getCallValue($this);
        } else {
            return parent::__call($name, $args);
        }
    }

    public function __get($name) {
        if (self::hasField($name)) {
            return self::getField($name)->getValue($this);
        } else {
            return parent::__get($name);
        }
    }
}
