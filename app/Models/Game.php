<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Game extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'image',
        'isActive',
    ];

    /**
     * Relación many-to-many con usuarios
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'game_user')
            ->withPivot('best_score', 'best_time', 'isFavorite')
            ->withTimestamps();
    }
}
