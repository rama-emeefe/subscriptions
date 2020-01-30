<?php
namespace Emeefe\Subscriptions\Tests\Models;

//use Actuallymab\LaravelComment\CanComment;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //use CanComment;

    protected $guarded = [];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public $timestamps = false;

    /*public function canCommentWithoutApprove(): bool
    {
        return $this->is_admin;
    }*/
}
