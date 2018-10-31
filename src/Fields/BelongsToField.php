<?php

namespace NastuzziSamy\Laravel\Fields;

use NastuzziSamy\Laravel\Interfaces\IsACallableField;

class BelongsToField extends IntegerField implements IsACallableField
{
    protected $default = 1;
    protected $relatedModel;

    public function __construct($name = null) {
        parent::__construct($name);
    }

    public function to($relatedModel)
    {
        $this->relatedModel = $relatedModel;

        return $this;
    }

    public function getCallValue($model)
    {
        return $model->belongsTo($this->relatedModel, $this->name.'_id');
    }

    public function getValue($model)
    {
        return $this->call($model)->get();
    }
}
