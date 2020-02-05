<?php
namespace Emeefe\Subscriptions\Tests\Models;

use Emeefe\Subscriptions\Traits\CanSubscribe;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use CanSubscribe;

    public $timestamps = false;
}
