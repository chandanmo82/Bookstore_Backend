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
    public function bookAlreadyAddedOrNot($book_id, $user_id)
    {
        $bookCart = Cart::where([
            ['book_id', '=', $book_id],
            ['user_id', '=', $user_id]
        ])->first();
        return $bookCart;
    }
    public function leftJoinBookWithCart($user_id){
        $result = Cart::leftJoin('books', 'carts.book_id', '=', 'books.id')
        ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.Price', 'carts.book_quantity')
        ->where('carts.user_id', '=', $user_id)
        ->get();
        return $result;
    }
    public function getCartId($cart_id){
        $result = Cart::find($cart_id);
        return $result;
    }
}
