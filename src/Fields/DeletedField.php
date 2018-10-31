<?php

namespace NastuzziSamy\Laravel\Fields;

class DeletedField extends TimestampField
{
    protected $fillable = false;
    protected $nullable = true;
}
