<?php

namespace NastuzziSamy\Laravel\Fields;

class IncrementField extends IntegerField
{
    protected $priority = 100;

    protected $fillable = false;
    protected $increments = true;
    protected $default = 1;
}
