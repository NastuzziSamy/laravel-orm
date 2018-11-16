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
    public const NO_BLANK = 512;

    /* If the string is too long, auto cut at the defined length */
    public const CARACTERE_RESIZE = 1024;

    /* If the string is too long, auto cut at the last word before the defined length */
    public const WORD_RESIZE = 2048;

    /* If the string is too long, auto cut at the last sentence before the defined length */
    public const SENTENCE_RESIZE = 4096;

    /* If the string is too long, auto cut and add dots */
    public const DOTS_ON_RESIZING = 8192;

    public function __construct(int $length = null)
    {
        parent::__construct();

        if ($length) {
            $this->length($length);
        }
    }

    public function length(int $length) {
        $this->checkLock();

        $this->length = $length;
        $this->properties['length'] = $length;

        return $this;
    }

    public function getValue($model, $value) {
        return is_null($value) ? $value : (string) $value;
    }

    public function setValue($model, $value) {
        return is_null($value) ? $value : (string) $value;
    }
}
