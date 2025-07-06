<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epresence extends Model
{
    use HasFactory;

    protected $table = 'epresence';

    protected $fillable = [
        'user_id',
        'type',
        'is_approve',
        'waktu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
