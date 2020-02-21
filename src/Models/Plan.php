<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;

class Plan extends Model implements PlanInterface{

    protected $casts = [
        'metadata' => 'array'
    ];
    public function type(){
        return $this->belongsTo(PlanType::class, 'plan_type_id');
    }

    public function features(){

    }

    public function scopeByType($query, string $type){

    }

    public function scopeVisible($query){

    }

    public function scopeHidden($query){

    }

    public function assignFeatureLimitByCode(int $limit, string $featureCode){

    }

    public function getFeatureLimitByCode() {

    }

    public function hasFeature(string $featureCode){

    }

    public function isVisible() {

    }

    public function isHidden() {

    }

    public function isDefault() {

    }

    public function setAsVisible() {

    }

    public function setAsHidden() {

    }

    public function setAsDefault() {
        
    }

    // public static function boot() {
    //     static::saving(function($plan){
    //         $count = $this->type->plans()->where('is_default', 1)->count();
    //         if($count == 0) {
    //             //TODO Asignar el valor de is_default
    //         } else {
    //             //TODO eliminar el que estaba por defecto y poner al nuevo el is_default
    //         }
    //     });
    // }
}