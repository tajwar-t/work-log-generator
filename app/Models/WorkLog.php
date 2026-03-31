<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'user_id',
        'log_type',
        'log_date',
        'section_a_items',
        'section_b_items',
        'generated_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
