<?php

namespace NastuzziSamy\Laravel\Fields;

class CreatedField extends TimestampField
{
    protected $priority = 1;

    protected $fillable = false;
    // protected $default = DB::raw('CURRENT_TIMESTAMP');
}
