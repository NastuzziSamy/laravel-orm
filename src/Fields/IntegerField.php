<?php

namespace LaravelORM\Fields;

class IntegerField extends Field
{
    protected $type = 'integer';

    /**
     * Set of rules.
     * Common to all integer fields.
     *
     * @var integer
     */
    /* Indicate the value is unsigned (positive) */
    public const UNSIGNED = 512;

    /* Indicate the value is positive */
    public const POSITIVE = self::UNSIGNED;

    /* Indicate the value is negative */
    protected const NEGATIVITY = 1024;
    public const NEGATIVE = self::NEGATIVITY | self::UNSIGNED;

    /* Except if the sign value is the wrong one */
    public const NEED_SIGN = 2048;

    /* Except if the value is 0 */
    public const NOT_ZERO = 4096;

    public function __construct($rules = 'DEFAULT_FIELD', $default = null) {
        return parent::__construct($rules, $default);
    }

    protected function addRule(int $rule) {
        $this->checkLock();

        if (($rule & self::UNSIGNED) === self::UNSIGNED) {
            $this->unsigned(true, !$this->hasRule(self::NEGATIVITY));
        }

        return parent::addRule($rule);
    }

    public function unsigned(bool $unsigned = true, bool $positive = true) {
        $this->checkLock();

        $this->properties['unsigned'] = $unsigned;

        if ($unsigned) {
            if ($positive) {
                return $this->positive();
            }

            return $this->negative();
        }
        else {
            return $this->removeRule(self::NEGATIVE);
        }
    }

    public function positive() {
        $this->checkLock();

        $this->rules |= self::POSITIVE;
        $this->removeRule(self::NEGATIVITY);

        return $this;
    }

    public function negative() {
        $this->checkLock();

        $this->rules |= self::NEGATIVE;

        return $this;
    }

    public function castValue($value) {
        return (int) $value;
    }

    public function setValue($model, $value) {
        $value = parent::setValue($model, $value);

        if ($value === 0) {
            if ($this->hasRule(self::NOT_ZERO, self::STRICT)) {
                throw new \Exception('Cannot set the value 0 for the field `'.$this->name.'`');
            }
        }
        else if ($this->hasRule(self::UNSIGNED)) {
            $newValue = abs($value);

            if ($this->hasRule(self::NEGATIVITY)) {
                $newValue = - $newValue;
            }

            if ($newValue !== $value && $this->hasRule(self::NEED_SIGN, self::STRICT)) {
                throw new \Exception('The value is must be '.($this->hasRule(self::NEGATIVITY) ? 'negative' : 'positive').' for the field `'.$this->name.'`');
            }

            $value = $newValue;
        }

        return $value;
    }
}
