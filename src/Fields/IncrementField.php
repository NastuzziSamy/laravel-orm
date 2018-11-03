<?php

namespace NastuzziSamy\Laravel\Fields;

use NastuzziSamy\Laravel\Interfaces\IsAPrimaryField;

class IncrementField extends IntegerField implements IsAPrimaryField
{
    protected $column;
    protected $fillable = false;

    public function __construct()
    {
        parent::__construct(true);
    }

    public function _getDefaultProperties(): array
    {
        return array_merge(
            parent::_getDefaultProperties(),
            [
                'autoIncrement' => true,
            ]
        );
    }
}
