<?php

namespace LaravelORM\Traits;

use LaravelORM\Schema;
use LaravelORM\CompositeFields\CompositeField;
use LaravelORM\Builder;
use Illuminate\Support\Str;

Trait HasORM {
    protected static $schema;

    public function __construct(...$args) {
        return parent::__construct(...$args);

        $schema = self::getSchema();

        $this->fillable = $schema->getFillableFields();
        $this->visible = $schema->getVisibleFields();

        $this->setPrimary($schema->getPrimary());

        $this->buildFieldsScopes();
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

    public function __call($method, $args) {
        $key = Str::snake($method);

        if (self::getSchema()->has($key)) {
            $field = self::getSchema()->get($key);

            if (count($args) === 0) {
                return $field->relationValue($this);
            }
            else {
                return $field->whereValue($this, ...$args);
            }
        } else {
            return parent::__call($method, $args);
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates()) &&
            ! is_null($value)) {
            return $this->asDateTime($value);
        }

        $key = Str::snake($key);

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (self::getSchema()->has($key)) {
            return self::getSchema()->get($key)->getValue($this, $value);
        }

        return $value;
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        if (self::getSchema()->has($key)) {
            return $this->getRelationshipFromSchema($key);
        }
    }

    /**
     * Get a relationship value from the schema.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromSchema($key)
    {
        return tap(self::getSchema()->get($key)->getValue($this, $key), function ($results) use ($key) {
            $this->setRelation($key, $results);
        });
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        elseif ($value && $this->isDateAttribute($key)) {
            $this->attributes[$key] = $this->fromDateTime($value);

            return $this;
        }

        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $this->attributes[$key] = $this->castAttributeAsJson($key, $value);

            return $this;
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $key = Str::snake($key);

        if (self::getSchema()->has($key)) {
            $field = self::getSchema()->get($key);

            $value = $field->setValue($this, $value);

            if ($field instanceof CompositeField) {
                return $this;
            }
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function __toString() {
        return (string) $this->getKey();
    }
}
