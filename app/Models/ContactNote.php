<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactNote extends Model
{
    protected $fillable = ['owner_id', 'contact_id', 'description'];
}
