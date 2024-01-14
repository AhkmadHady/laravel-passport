<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table       = 'customer';
    protected $fillable    = ['name','email','phone','address','created_by','updated_by'];
    protected $hidden      = ['created_at','updated_at','created_by','updated_by'];
}
