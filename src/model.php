<?php

namespace LaravelORM;

use LaravelORM\Traits\LaravelORM;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use LaravelORM;

    protected static $schema;
}
