<?php

namespace App;

class Meta
{
    public $fields = [];

    public function __construct($model) {
        $this->model = $model;
    }
}
