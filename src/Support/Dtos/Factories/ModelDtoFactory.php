<?php

namespace Lucent\Support\Dtos\Factories;

use DTOs\Dto;
use DTOs\Factories\DtoFactory;
use Illuminate\Database\Eloquent\Model;

class ModelDtoFactory extends DtoFactory
{
    /**
     * Creates a dto object based on model's fillable attributes.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null                         $dtoClass
     * @return \DTOs\Dto
     */
    public static function makeFromModel(Model $model, ?string $dtoClass = null): Dto
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

        foreach($model->relationsToArray() as $key => $value){
            $attributes[$key] = $value;
        }

        return static::make($attributes, $dtoClass);
    }
}
