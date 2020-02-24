<?php

namespace Emeefe\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Emeefe\Subscriptions\Contracts\PlanInterface;

class Plan extends Model implements PlanInterface{

    protected $casts = [
        'metadata' => 'array',
        'is_default' => 'boolean'
    ];

    public function type(){
        return $this->belongsTo(PlanType::class, 'plan_type_id');
    }

    public function features(){

    }

    public function scopeByType($query, string $type){

    }

    public function scopeVisible($query){
        return $query->where('is_visible',1);
    }

    public function scopeHidden($query){
        return $query->where('is_visible',0);
    }

    public function assignFeatureLimitByCode(string $featureCode, int $limit){

    }

    public function getFeatureLimitByCode() {

    }

    public function hasFeature(string $featureCode){

    }

    public function isVisible() {
        if($this->is_visible) {
            return true;
        }
        return false;
    }

    public function isHidden() {
        if($this->is_visible) {
            return false;
        }
        return true;
    }

    public function isDefault() {
        if($this->is_default) {
            return true;
        }
        return false;
    }

    public function setAsVisible() {
        $this->is_visible = true;
        $this->save();
    }

    public function setAsHidden() {
        $this->is_visible = false;
        $this->save();
    }

    public function setAsDefault() {
        $this->is_default = true;
        $this->save();
    }

}