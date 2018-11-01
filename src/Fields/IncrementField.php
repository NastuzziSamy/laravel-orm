<?php

namespace NastuzziSamy\Laravel\Fields;

class IncrementField extends IntegerField
{
    protected $column;
    protected $fillable = false;

    public function __construct(string $name = null)
    {
        parent::__construct(true, $name);
    }

    public function getDefaultProperties(): array
    {
        return array_merge(
            parent::getDefaultProperties(),
            [
                'autoIncrement' => true,
            ]
        );
    }
}
