<?php

namespace LaravelORM\Traits;

trait IsPrepared
{
    protected $preparing = false;
    protected $prepared = false;


    public function prepare() {
        if ($this->isPrepared()) {
            throw new \Exception('The field '.$this->getName().' is already prepared');
        }

        $this->preparing = true;

        $shortname = (new \ReflectionClass(self::class))->getShortName();

        // prepareComposite, prepareSchema
        $this->{'prepare'.preg_split('/(?=[A-Z])/', $shortname)[1]}();

        $this->prepared = true;
        $this->preparing = false;

        return $this;
    }

    public function isPreparing() {
        return $this->preparing;
    }

    public function isPrepared() {
        return $this->prepared;
    }
}
