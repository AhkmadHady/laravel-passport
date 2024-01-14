<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table       = 'invoice_detail';
    protected $fillable    = ['invoice_id','id_items','price','quntity','total','created_by','updated_by'];
    protected $hidden      = ['created_at','updated_at','created_by','updated_by'];

    public function Item()
    {
        return $this->hasOne(Item::class, 'id','id_items');
    }
}
