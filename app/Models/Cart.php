<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = "carts";
    protected $fillable = ['book_id'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function adminOrUserVerification($currentUserId)
    {
        $userId = User::select('id')->where([['role', '=', 'user'], ['id', '=', $currentUserId]])->get();
        return $userId;
    }
    public function bookExistOrNot($book_id)
    {
        $book_existance = Book::select('quantity')->where([
            ['id', '=', $book_id]
        ])->get();
        return $book_existance;
    }
}
