<?php

namespace NastuzziSamy\Laravel\Fields;

class UpdatedField extends TimestampField
{
    protected $priority = 0;

    protected $fillable = false;
    // protected $default = DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP');
}
