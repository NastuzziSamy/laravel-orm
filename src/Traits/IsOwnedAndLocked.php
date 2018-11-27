<?php

namespace LaravelORM\Traits;

trait IsOwnedAndLocked
{
    protected $name;

    protected $owner;
    protected $locked = false;


    public function getName() {
        return $this->name;
    }

    protected function setName($value) {
        $this->name = $value;

        return $this;
    }

    public function own($owner, string $name) {
        if ($this->isOwned()) {
            throw new \Exception('The field '.$this->getName().' has already an owner');
        }

        $this->setName($name);

        $this->owner = $owner;

        return $this;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function isOwned() {
        return (bool) $this->getOwner();
    }

    public function lock() {
        $this->checkLock();

        if (!$this->isOwned()) {
            throw new \Exception('The field has no owner, cannot lock it');
        }

        $this->locked = true;

        return $this;
    }

    public function isLocked() {
        return $this->locked;
    }

    public function checkLock() {
        if ($this->isLocked()) {
            throw new \Exception('The field is locked, nothing can change');
        }

        return $this;
    }
}
