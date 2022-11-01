<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Profile extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telephone',
        'birthday',
        'image_url',
        'whatsapp',
        'share_personal_data',
        'marketing_messages',
        'user_id'
    ];
    
    public function user()
    {
    return $this->belongsTo(User::class);
    } 
}
