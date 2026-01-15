<?php

namespace Lucent\Support\Dtos\Contracts;

use Lucent\Support\Dtos\Workshop\ForbidsOverrides;
use Lucent\Support\Dtos\Workshop\IgnoresUnknownAttributes;
use Lucent\Support\Dtos\Workshop\ReadOnlyAttributes;

trait DtoOptions
{
    private $options = [
        'forbids_overrides' => false,
        'ignores_unknown' => false,
        'read_only' => false,
    ];

    protected function initializeDtoOptions(): void
    {
        $ref = new \ReflectionClass(static::class);

        $this->options = [
            'forbids_overrides' => $ref->implementsInterface(ForbidsOverrides::class),
            'ignores_unknown' => $ref->implementsInterface(IgnoresUnknownAttributes::class),
            'read_only' => $ref->implementsInterface(ReadOnlyAttributes::class),
        ];
    }

    protected function option(string $key)
    {
        return $this->options[$key] ?? false;
    }

    public function setOptions(array $options): static
    {
        foreach ($options as $key => $value) {
            if(isset($this->options[$key])){
                $this->options[$key] = (bool) $value;
            }
        }

        return $this;
    }

}
