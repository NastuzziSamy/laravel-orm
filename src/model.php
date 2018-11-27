<?php

namespace LaravelORM;

use LaravelORM\Traits\HasORM;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use HasORM;
}
