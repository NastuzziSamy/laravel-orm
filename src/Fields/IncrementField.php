<?php

namespace NastuzziSamy\Laravel\Fields;

use NastuzziSamy\Laravel\Interfaces\IsAPrimaryField;

class IncrementField extends IntegerField implements IsAPrimaryField
{
    protected $fillable = false;
    protected $unique = true;
    protected $increments = true;
    protected $default = 1;
}
