<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Wishlist;
use App\Exceptions\BookStoreAppException;

class WishlistController extends Controller
{
    /**
     * This Function will take book id as input and it will ad that book to wishlist
     * as per user's requirement
     */
    /**
     * @OA\Post(
     *   path="/api/auth/addtowishlist",
     *   summary="Add the book to wishlist",
     *   description=" Add to wishlist ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"book_id"},
     *               @OA\Property(property="book_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book added to wishlist Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function addBookToWishlistByBookId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::info('Invalid Input');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new Wishlist();
            $userId = $wishlist->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if ($currentUser) {
                $book_id = $request->input('book_id');
                $book_existance = Book::select('quantity')->where([
                    ['id', '=', $book_id]
                ])->get();

                if (!$book_existance) {
                    return response()->json(['message' => 'Book not Found In The Bookstore'], 404);
                }
                $book = Book::find($book_id);
                if ($book->quantity == 0) {
                    return response()->json(['message' => 'OUT OF STOCK From The BookStore'], 404);
                }
                $book_wishlist = Wishlist::where([
                    ['book_id', '=', $request->input('book_id')],
                    ['user_id', '=', $currentUser->id]
                ])->first();

                if ($book_wishlist) {
                    return response()->json(['message' => 'Book already added to Wishlist'], 404);
                }

                $wishlist = new Wishlist();
                $wishlist->book_id = $request->get('book_id');

                if ($currentUser->wishlists()->save($wishlist)) {
                    return response()->json(['message' => 'Book added to wishlist Sucessfully'], 201);
                }
                $value = Cache::remember('wishlists', 3600, function () {
                    return DB::table('wishlists')->get();
                });
                return response()->json(['message' => 'Book cannot be added to wishlist'], 405);
            } else {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This Function will take wishlist Id as input and will perform the delete operation
     * for the perticular wishlist which the user want to delete from wishlist
     */
    /**
     * @OA\Post(
     *   path="/api/auth/deletewishlist",
     *   summary="Delete the book from wishlist",
     *   description=" Delete wishlist ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book deleted Sucessfully from wishlist"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function deleteBookByWishlistId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new Wishlist();
            $userId = $wishlist->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $book = $currentUser->wishlists()->find($id);
            if (!$book) {
                Log::error('Book Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Book not Found in wishlist'], 404);
            }

            if ($book->delete()) {
                Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                $value = Cache::remember('wishlists', 3600, function () {
                    return DB::table('wishlists')->get();
                });
                return response()->json(['message' => 'Book deleted Sucessfully from wishlist'], 201);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
    
    /**
     * This method will execute and return for the current user which books are added
     * in the wishlist and return all data
     */
    /**
     * @OA\Get(
     *   path="/api/auth/getwishlist",
     *   summary="Get All Books Present in wishlist",
     *   description=" Get All Books Present in wishlist ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function getAllBooksInWishlist()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new Wishlist();
            $userId = $wishlist->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if ($currentUser) {
                $books = Wishlist::leftJoin('books', 'wishlists.book_id', '=', 'books.id')
                    ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.Price', 'wishlists.book_quantity')
                    ->where('wishlists.user_id', '=', $currentUser->id)
                    ->get();

                if ($books == '[]') {
                    Log::error('Book Not Found');
                    return response()->json(['message' => 'Books not found'], 404);
                }
                Log::info('All book Presnet in wishlist are fetched');
                return response()->json([
                    'message' => 'Books Present in wishlist :',
                    'wishlist' => $books,

                ], 201);
            } else {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This function will take input as wishlist id and quantity from user and update
     * the quantity for the respective wishlist id and user
     */
    /**
     * @OA\Post(
     *   path="/api/auth/updatewishlist",
     *   summary="Add Quantity to Existing Book in wishlist",
     *   description=" Add Book Quantity  in wishlist",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "book_quantity"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="book_quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Quantity updated Successfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function updateBookQuantityInWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'book_quantity' => 'required|integer|min:1'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new Wishlist();
            $userId = $wishlist->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $wishlist = Wishlist::find($request->id);

            if (!$wishlist) {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $wishlist->book_quantity += $request->book_quantity;
            $wishlist->save();
            Log::info('Book Quantity updated Successfully to the bookstore wishlist');
            return response()->json([
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/auth/decreasewishlist",
     *   summary="Decrease Quantity to Existing Book in wishlist",
     *   description=" decrease Book Quantity  in wishlist",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "book_quantity"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="book_quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Quantity updated Successfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function decreaseBookQuantityInWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'book_quantity' => 'required|integer|min:1'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new Wishlist();
            $userId = $wishlist->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $wishlist = Wishlist::find($request->id);

            if (!$wishlist) {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $wishlist->book_quantity -= $request->book_quantity;
            $wishlist->save();
            Log::info('Book Quantity updated Successfully to the bookstore wishlist');
            return response()->json([
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
}
