<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators\Form;

class FormFieldOptions
{
    public bool $required;
    public bool $disabled;
    public bool $ommited;
    public bool $nullable;
    public mixed $defaultValue;
    public string $caption;
    public string $htmlId;

    /**
     * @param array|object $options
     */
    public function __construct(mixed $options)
    {
        $options = (array) $options;
        $properties = get_class_vars(__CLASS__);
        foreach ($properties as $prop => $default) {
            if (key_exists($prop, $options)) {
                $this->{$prop} = $options[$prop];
            }
        }
    }
}
