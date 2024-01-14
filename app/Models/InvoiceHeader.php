<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceHeader extends Model
{
    protected $table       = 'invoice_header';
    protected $fillable    = ['invoice_id','issue_date','due_date','subject','total_item','sub_total','tax','grand_total','id_customer','status','created_by','updated_by'];
    protected $hidden      = ['created_at','updated_at','created_by','updated_by'];

    public function InvoiceDetail()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id','invoice_id');
    }

    public function Customer()
    {
        return $this->hasOne(Customer::class, 'id','id_customer');
    }
}
