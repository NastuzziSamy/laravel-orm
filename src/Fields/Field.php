<?php

namespace NastuzziSamy\Laravel\Fields;

abstract class Field
{
    protected $priority = 50;

    protected $name;
    protected $type;
    protected $default;

    protected $fillable = true;
    protected $nullable = false;
    protected $increments = false;

    public function __construct($name)
    {
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function nullable($nullable = true)
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function increments()
    {
        $this->increments = $increments;

        return $this;
    }

    public function setDefault($value)
    {
        $this->default = $value;

        return $this;
    }
}
