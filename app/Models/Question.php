<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['question_text'];

    public function modes()
    {
        return $this->belongsToMany(Mode::class, 'mode_question');
    }
}
