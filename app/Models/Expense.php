<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',     // ✅ Logged-in user ID
        'title',
        'amount',
        'category',
        'paid_by',
        'date',
        'note',
        'is_shared',
    ];

    // ✅ Link expense to user (for group_code filtering)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
