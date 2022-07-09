<?php

declare(strict_types=1);

namespace Matronator\Generator\Form;

class FormField
{
    public string $type;

    public string $name;

    public string $label;

    public FormFieldOptions $options;

    public function __construct(string $type, string $name, string $label, ?FormFieldOptions $options = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
    }
}
