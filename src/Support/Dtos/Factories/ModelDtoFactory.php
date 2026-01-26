<?php

namespace Lucent\Support\Dtos\Factories;

use DTOs\Dto;
use DTOs\Factories\DtoFactory;
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Dtos\LaravelDto;
use InvalidArgumentException;

class ModelDtoFactory extends DtoFactory
{
    /**
     * Creates a dto object based on model's fillable attributes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null                         $dtoClass
     * @return \Lucent\Support\Dtos\LaravelDto
     */
    public static function makeFromModel(Model $model, ?string $dtoClass = null): LaravelDto
    {
        if(class_uses_trait(HasDtoFactory::class, $model) && is_null($dtoClass)){
            $dtoClass = $model->getDtoClass();
        }

        $attributes = [];

        foreach($model->getAttributes() as $key => $value){
            if($model->isFillable($key)){
                $attributes[$key] = $value;
            }
        }

        return static::make($attributes, $dtoClass);
    }

    protected static function validateDtoClass(string $dtoClass): void
    {
        parent::validateDtoClass($dtoClass);
        $reflection = new \ReflectionClass($dtoClass);
        if ( ! $reflection->isSubclassOf(LaravelDto::class)) {
            throw new InvalidArgumentException("Dto class $dtoClass must extend Lucent\Support\Dtos\LaravelDto");
        }
    }
}
