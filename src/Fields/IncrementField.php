<?php

namespace LaravelORM\Fields;

use LaravelORM\Interfaces\IsAPrimaryField;

class IncrementField extends IntegerField implements IsAPrimaryField
{
    protected $column;
    protected $fillable = false;

    public function __construct()
    {
        parent::__construct(true);

        $this->properties = [];
    }

    public function getMigration() {
        return array_merge([
            'increments' => $this->getName(),
        ], $this->getProperties());
    }
}
