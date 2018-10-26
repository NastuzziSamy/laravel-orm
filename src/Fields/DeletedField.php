<?php

namespace NastuzziSamy\Laravel\Fields;

class DeletedField extends TimestampField
{
    protected $priority = -1;

    protected $fillable = false;
    protected $nullable = true;
}
