<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $table = "books";
    protected $fillable = [
        'name',
        'description',
        'author',
        'image',
        'Price',
        'quantity'
    ];
    public function adminOrUserVerification($currentUserId)
    {
        $adminId = User::select('id')->where([['role', '=', 'admin'], ['id', '=', $currentUserId]])->get();
        return $adminId;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }
    public function getBookId($book_id)
    {
        $result = Book::find($book_id);
        return $result;
    }
    public function bookExistOrNot($name)
    {
        $bookDetails = Book::where('name', '=', $name)->first();
        return $bookDetails;
    }
    public function getQuantity($user_id, $name)
    {
        $quantity = Book::select('quantity')
            ->where([['books.user_id', '=', $user_id], ['books.name', '=', $name]])
            ->get();
        return $quantity;
    }
    public function getCurrentBookId($name)
    {
        $bookId =  Book::select('id')
            ->where([['books.name', '=', $name]])
            ->value('id');
        return $bookId;
    }
    public function getBookName($name)
    {
        return Book::select('name')
            ->where('name', '=', $name)
            ->value('name');
    }
    public function getBookAuthor($name)
    {
        return Book::select('author')
            ->where('name', '=', $name)
            ->value('author');
    }
    public function getBookPrice($name)
    {
        return Book::select('Price')
            ->where('name', '=', $name)
            ->value('Price');
    }

}
