<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'due_date', 'due_time', 'is_done' , 'is_shared',
    ];

    // ✅ Relationship: Each task belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
