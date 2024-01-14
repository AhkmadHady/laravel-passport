<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table       = 'item';
    protected $fillable    = ['item_name','type','price','description','created_by','updated_by'];
    protected $hidden      = ['created_at','updated_at','created_by','updated_by'];
}
