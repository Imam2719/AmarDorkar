<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'mobile', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * FIX: User model-এ reviews() hasMany relation যোগ করা হয়েছে।
     * ProductReview model-এ belongsTo(User::class) আছে কিন্তু inverse ছিল না।
     * User dashboard/profile-এ user-এর সব reviews দেখাতে এটি প্রয়োজন।
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
