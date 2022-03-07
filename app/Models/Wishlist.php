<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    protected $table = "wishlists";
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
    public function BookAlreadyAddedOrNot($book_id, $user_id)
    {
        $bookWishlist = Wishlist::where([
            ['book_id', '=', $book_id],
            ['user_id', '=', $user_id]
        ])->first();
        return $bookWishlist;
    }
    public function leftJoinBookWithWishlist($user_id)
    {
        $wishlistContents = Wishlist::leftJoin('books', 'wishlists.book_id', '=', 'books.id')
            ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.Price', 'wishlists.book_quantity')
            ->where('wishlists.user_id', '=', $user_id)
            ->get();
        return $wishlistContents;    
    }
    public function getWishlistid(){
        
    }
}
