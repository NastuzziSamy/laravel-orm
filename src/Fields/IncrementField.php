<?php

namespace LaravelORM\Fields;

use LaravelORM\Interfaces\IsAPrimaryField;

class IncrementField extends IntegerField implements IsAPrimaryField
{
    protected $type = 'increments';
    protected $column;
    protected $fillable = false;

    /* Default rules */
    public const DEFAULT_INCREMENT = self::NOT_NULLABLE | self::VISIBLE | self::NOT_ZERO | self::UNSIGNED | self::POSITIVE | self::NEED_SIGN;

    public function __construct($rules = 'DEFAULT_INCREMENT', $default = null)
    {
        parent::__construct($rules, $default);
    }

    public function unsigned(bool $unsigned = true, bool $positive = true) {
        $return = parent::unsigned($unsigned, $positive);

        if ($unsigned) {
            unset($this->properties['unsigned']);
        }

        return $return;
    }
}
