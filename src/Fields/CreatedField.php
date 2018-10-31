<?php

namespace NastuzziSamy\Laravel\Fields;

class CreatedField extends TimestampField
{
    protected $fillable = false;
    // protected $default = DB::raw('CURRENT_TIMESTAMP');
}
