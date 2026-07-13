<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isVip(): bool
    {
        return false;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}


// [12:25 PM, 7/10/2026] Br Doctor: There is product table
// [12:26 PM, 7/10/2026] Br Doctor: Product category
// [12:26 PM, 7/10/2026] Br Doctor: Product sizes
// [12:26 PM, 7/10/2026] Br Doctor: Product prices
// [12:26 PM, 7/10/2026] Br Doctor: Once those are in place
// [12:27 PM, 7/10/2026] Br Doctor: We then have stock, stock items (stock items are products you want to stock and their sizes
// [12:27 PM, 7/10/2026] Br Doctor: When you lock a stock the system automatically generates product prices
// [12:29 PM, 7/10/2026] Br Doctor: Now if customer sends us their product in csv or pdf we can then build something that will read these details, load it into the right product, size and stock it
// [12:29 PM, 7/10/2026] Br Doctor: The lock the stock so it updates on the site