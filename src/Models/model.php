<?php

namespace App\Models;

class Model
{
    protected static $fields;

    // abstract protected static function __fields($fields, $field);

    // TODO: Gérer le camel/snake case pour group_id ou groupId
    protected static function __fields(IncrementField $id, StringField $name = null, BelongsToField $group, CreatedField $created)
    {
        $group->setName('custom_id');
    }

    protected static function __options($table = '')
    {
        // A voir
    }

    public static function getFields()
    {
        if (!self::$fields) {
            self::$fields = new Fields(self::class);
            $method = new \ReflectionMethod(self::class, '__fields');
            $parameters = $method->getParameters();

            $fields = [];
            $args = [];

            foreach ($parameters as $key => $parameter) {
                $name = $parameter->name;

                if (self::$fields->hasField($name)) {
                    $fields[$name] = self::$fields->getField($name);
                } elseif ($parameter->hasType()) {
                    $class = (string) $parameter->getType();
                    $fields[$name] = new $class($name);
                } else {
                    throw new \Exception('Type nécessaire');
                }

                if ($parameter->allowsNull()) {
                    $fields[$name]->nullable();
                } elseif ($parameter->isOptional()) {
                    $fields[$name]->setDefault($parameter->getDefaultValue());
                }

                $args[] = &$fields[$name];
            }

            self::__fields(...$args);

            foreach ($fields as $name => $field) {
                self::$fields->setField($name, $field);
            }
        }

        return self::$fields;
    }
}
