<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Internal back-office user (staff), distinct from Merchant.
 * Authenticates the Blade admin panel via the "admin" guard - see
 * config/auth.php and app/Http/Middleware/Kernel.php ("auth" alias).
 */
class Admin extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory;

    protected $fillable = [
        "name",
        "email",
        "password",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "password" => "hashed",
    ];
}
