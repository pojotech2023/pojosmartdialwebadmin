<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappContact extends Model
{
    protected $fillable = ['name', 'mobile'];

    public function recipients()
    {
        return $this->hasMany(WhatsappNotificationRecipient::class);
    }
}
