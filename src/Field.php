<?php

namespace LaravelORM;

class Field {
    /**
     * Set of rules.
     * Common to all fields.
     *
     * @var integer
     */

    /* Indicate that no rules are applied */
    public const NONE = 0;

    /* Tolerate mode: tolerate a rule */
    public const TOLERATE = 0;

    /* Strict mode: will throw an exception for each error. Pass over everthing */
    public const STRICT = 1;

    /* Indicate that the field accepts nullable values */
    public const NULLABLE = 2;

    /* Except if trying to set a nullable value */
    public const NOT_NULLABLE = 4;

    /* Indicate it is visible by default */
    public const VISIBLE = 8;

    /* Indicate it is fillable by default */
    public const FILLABLE = 16;

    /* Default rules */
    public const DEFAULT_FIELD = self::NOT_NULLABLE | self::VISIBLE | self::FILLABLE;


    public function hasRule(int $rule, int $jokerRule = null) {
        return ($this->rules & $rule) === $rule
            || (!is_null($jokerRule) && (($this->rules & $jokerRule) === $jokerRule));
    }

    protected function addRule(int $rule) {
        $this->checkLock();

        if (($rule & self::NULLABLE) === self::NULLABLE) {
            $this->nullable();
        }

        $this->rules |= $rule;

        return $this;
    }

    protected function removeRule(int $rule) {
        $this->checkLock();

        if ($this->hasRule($rule)) {
            $this->rules ^= $rule;
        }

        return $this;
    }

    protected function getRule(string $name) {
        return constant(static::class.'::'.$name);
    }

    public function addRules($rules) {
        $this->checkLock();

        if (is_array($rules)) {
            foreach ($rules as $rule) {
                return $this->addRule($this->getRule($rule));
            }

            return $this;
        }

        if (is_string($rules)) {
            return $this->addRule($this->getRule($rules));
        }

        return $this->addRule($rules);
    }

    public static function getAvailableRules() {
        $reflectionClass = new \ReflectionClass(static::class);
        $rules = $reflectionClass->getConstants();

        asort($rules);

        return $rules;
    }

    public function getRules() {
        $rules = self::getAvailableRules();

        foreach ($rules as $key => $value) {
            if (!$this->hasRule($value)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }
}
