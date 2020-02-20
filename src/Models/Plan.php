<?php

namespace Emeefe\Subscriptions\Models;
use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;

class Plan extends Model implements PlanInterface{

    protected $casts = [
        'metadata' => 'array'
    ];
    public function type(){
        return $this->belongsTo(PlanType::class);
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
}