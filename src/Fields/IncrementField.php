<?php

namespace LaravelORM\Fields;

use LaravelORM\Interfaces\IsAPrimaryField;

class IncrementField extends IntegerField implements IsAPrimaryField
{
    protected $column;
    protected $fillable = false;

    /* Default rules */
    public const DEFAULT_INCREMENT = self::NOT_NULLABLE | self::VISIBLE | self::NOT_ZERO | self::POSITIVE | self::NEED_SIGN;

    public function __construct(int $rules = self::DEFAULT_INCREMENT, $default = null)
    {
        parent::__construct($rules, $default);
    }

    public function getMigration() {
        return array_merge([
            'increments' => $this->getName(),
        ], $this->getProperties());
    }
}
