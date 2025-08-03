<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'dob',
        'languages',
        'country',
        'state',
        'city',
        'password',
        'email_verified',
        'email_token',
        'otp'
    ];

    protected $hidden = ['password', 'remember_token'];
}
?>