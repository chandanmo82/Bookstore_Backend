<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $table="addresses";
    protected $fillable = [
        'user_id',
        'address',
        'city',
        'state',
        'landmark',
        'pincode',
        'addresstype'
        
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function getUserAddress($user_id){
        $user = Address::select('addresses.id', 'addresses.user_id', 'addresses.address', 'addresses.city', 'addresses.state', 'addresses.landmark', 'addresses.pincode', 'addresses.addresstype')
                    ->where([['addresses.user_id', '=', $user_id]])
                    ->get();
        return $user;            

    }
    public function addressExist($userId) {
        return Address::where('id', $userId)->first();
    }

}
