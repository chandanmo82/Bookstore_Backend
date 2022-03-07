<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Cart;
use App\Exceptions\BookStoreAppException;

class CartController extends Controller
{
    /**
     * This Function will take book id as input and it will ad that book to cart
     * as per user's requirement
     */
    /**
     * @OA\Post(
     *   path="/api/auth/addtocart",
     *   summary="Add the book to Cart",
     *   description=" Add to cart ",
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
     *   @OA\Response(response=201, description="Book added to Cart Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function addBookToCartByBookId(Request $request)
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
            $cart = new Cart();
            $bookObject = new Book();
            $userId = $cart->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if ($currentUser) {
                $book_id = $request->input('book_id');
                $user_id = $currentUser->id;
                $book_existance = $cart->bookExistOrNot($book_id);

                if (!$book_existance) {
                    return response()->json(['message' => 'Book not Found In the Book store'], 404);
                }
                $book = $bookObject->getBookId($book_id);
                if ($book->quantity == 0) {
                    return response()->json(['message' => 'OUT OF STOCK In the Bookstore'], 404);
                }
                $book_cart = $cart->bookAlreadyAddedOrNot($book_id, $user_id);
                if ($book_cart) {
                    return response()->json(['message' => 'Book already added to Cart'], 404);
                }
                $cart = new Cart;
                $cart->book_id = $request->get('book_id');

                if ($currentUser->carts()->save($cart)) {
                    return response()->json(['message' => 'Book added to Cart Sucessfully'], 201);
                }
                $value = Cache::remember('carts', 3600, function () {
                    return DB::table('carts')->get();
                });
                return response()->json(['message' => 'Book cannot be added to Cart'], 405);
            } else {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This Function will take cart Id as input and will perform the delete operation
     * for the perticular cart which the user want to delete from cart
     */
    /**
     * @OA\Post(
     *   path="/api/auth/deletecart",
     *   summary="Delete the book from cart",
     *   description=" Delete cart ",
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
     *   @OA\Response(response=201, description="Book deleted Sucessfully from cart"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function deleteBookByCartId(Request $request)
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
            $cart = new Cart();
            $userId = $cart->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $book = $currentUser->carts()->find($id);
            if (!$book) {
                Log::error('Book Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Book not Found in cart'], 404);
            }

            if ($book->delete()) {
                Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                $value = Cache::remember('carts', 3600, function () {
                    return DB::table('carts')->get();
                });
                return response()->json(['message' => 'Book deleted Sucessfully from cart'], 201);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This method will execute and return for the current user which books are added
     * in the cart and return all data
     */
    /**
     * @OA\Get(
     *   path="/api/auth/getcart",
     *   summary="Get All Books Present in Cart",
     *   description=" Get All Books Present in Cart ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function getAllBooksByUserId()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $cart = new Cart();
            $userId = $cart->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if ($currentUser) {
                $user_id = $currentUser->id;
                $books = $cart->leftJoinBookWithCart($user_id);
                if ($books == '[]') {
                    Log::error('Book Not Found');
                    return response()->json(['message' => 'Books not found'], 404);
                }
                Log::info('All book Presnet in cart are fetched');
                return response()->json([
                    'message' => 'Books Present in Cart :',
                    'Cart' => $books,

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
     * This function will take input as cart id and quantity from user and update
     * the quantity for the respective cart id and user
     */
    /**
     * @OA\Post(
     *   path="/api/auth/updatequantity",
     *   summary="Add Quantity to Existing Book in cart",
     *   description=" Add Book Quantity  in cart",
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
    public function updateBookQuantityInCart(Request $request)
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
            $cartObject = new Cart();
            $userId = $cartObject->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $cart_id = $request->id ;
            $cart = $cartObject->getCartId($cart_id);
            if (!$cart) {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $cart->book_quantity += $request->book_quantity;
            $cart->save();
            Log::info('Book Quantity updated Successfully to the bookstore cart');
            return response()->json([
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This function will take input as cart id and quantity from user and update
     * the quantity for the respective cart id and user
     */
    /**
     * @OA\Post(
     *   path="/api/auth/decreasequantity",
     *   summary="Decrease Quantity to Existing Book in cart",
     *   description=" Decrease Book Quantity  in cart",
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
    public function decreaseBookQuantityInCart(Request $request)
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
            $cartObject = new Cart();
            $userId = $cartObject->adminOrUserVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'You are not an User'], 404);
            }
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
            $cart_id = $request->id ;
            $cart = $cartObject->getCartId($cart_id);
            if (!$cart) {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $cart->book_quantity -= $request->book_quantity;
            $cart->save();
            Log::info('Book Quantity Decreased Successfully to the bookstore cart');
            return response()->json([
                'message' => 'Book Quantity Decreased Successfully'
            ], 201);
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
}
