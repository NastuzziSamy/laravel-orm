<?php

namespace LaravelORM\Fields;

class StringField extends Field
{
    protected $type = 'string';
    protected $length;

    /**
     * Set of rules.
     * Common to all string fields.
     *
     * @var integer
     */

    /* If the string is a blank value, throw an exception */
    public const NOT_BLANK = 512;

    /* Except if the length is longer than allowed */
    public const RESPECT_LENGTH = 1024;

    /* If the string is too long, auto cut at the defined length */
    public const CARACTERE_RESIZE = 2048;

    /* If the string is too long, auto cut at the last word before the defined length */
    public const WORD_RESIZE = 4096;

    /* If the string is too long, auto cut at the last sentence before the defined length */
    public const SENTENCE_RESIZE = 8192;

    /* If the string is too long, auto cut and add dots */
    public const DOTS_ON_RESIZING = 16384;

    /* Default rules */
    public const DEFAULT_STRING = self::NOT_NULLABLE | self::NOT_BLANK | self::VISIBLE | self::FILLABLE;

    public function __construct($rules = 'DEFAULT_STRING', $default = null) {
        parent::__construct($rules, $default);
    }

    public function lock() {
        if ($this->hasRule(self::RESPECT_LENGTH) && is_null($this->length)) {
            throw new \Exception('No length set for '.$this->name);
        }

        return parent::lock();
    }

    public function length(int $length) {
        $this->checkLock();

        if ($length <= 0) {
            throw new \Exception('The length must be a positive number');
        }

        $this->length = $length;
        $this->properties['length'] = $length;

        return $this;
    }

    protected function castValue($value) {
        return is_null($value) ? $value : (string) $value;
    }

    public function setValue($model, $value) {
        $value = parent::setValue($model, $value);

        if ($this->length < strlen($value) && !is_null($value)) {
            $dots = $this->hasRule(self::DOTS_ON_RESIZING) ? '...' : '';

            if ($this->hasRule(self::RESPECT_LENGTH, self::STRICT)) {
                throw new \Exception('The value must respect the defined length for the field '.$this->name);
            }

            if ($this->hasRule(self::CARACTERE_RESIZE)) {
                $value = $this->resizeValue($value, $this->length, '', $dots);
            }

            if ($this->hasRule(self::WORD_RESIZE)) {
                $value = $this->resizeValue($value, $this->length, ' ', $dots);
            }

            if ($this->hasRule(self::SENTENCE_RESIZE)) {
                $value = $this->resizeValue($value, $this->length, '.', $dots);
            }
        }

        if ($this->hasRule(self::NOT_BLANK, self::STRICT)) {
            throw new \Exception('The value cannot be empty for the field '.$this->name);
        }

        return $value;
    }

    public function resizeValue($value, $length, $delimiter = '', $toAdd = '...') {
        $parts = $delimiter === '' ? str_split($value) : explode($delimiter, $value);
        $valides = [];
        $length -= strlen($toAdd);

        foreach ($parts as $part) {
            if (strlen($part) <= $length) {
                $length -= strlen($part);
                $valides[] = $part;
            }
            else {
                break;
            }
        }

        return implode($delimiter, $valides).$toAdd;
    }
}
