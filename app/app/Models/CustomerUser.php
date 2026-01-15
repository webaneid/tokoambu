<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerUser extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'name',
        'phone',
        'whatsapp_number',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer associated with this user.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'email', 'email');
    }

    /**
     * Get the cart associated with this customer user.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_user_id');
    }

    /**
     * Get the orders associated with this customer user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_user_id');
    }

    /**
     * Get the wishlists associated with this customer user.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'customer_user_id');
    }
}
