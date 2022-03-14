<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Order;
use App\Models\Book;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BookStoreAppException;
use App\Http\Requests\SendEmailRequest;
use App\Notifications\PlaceOrder;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/placeorder",
     *   summary="Place  Order",
     *   description=" Place a order ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name", "quantity"},
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Order Successfully Placed..."),
     *   @OA\Response(response=401, description="We Do not have this book in the store..."),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'name' => 'required',
            'quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $book = new Book();
                $address = new Address();
                $get_book = $book->bookExistOrNot($request->input('name'));
                if ($get_book == '') {
                    Log::error('Book is not available');
                    throw new BookStoreAppException("We Do not have this book in the store...", 401);
                }
                $get_quantity = $book->getQuantity($currentUser->id, $request->input('name'));
                if ($get_quantity < $request->input('quantity')) {
                    Log::error('Book stock is not available');
                    throw new BookStoreAppException("This much stock is unavailable for the book", 401);
                }
                //getting bookID
                $get_bookid = $book->getCurrentBookId($request->input('name'));

                //getting addressID
                $get_addressid = $request->input('address_id');
                //get book name..    
                $get_BookName = $book->getBookName($request->input('name'));

                //get book author ...
                $get_BookAuthor = $book->getBookAuthor($request->input('name'));

                //get book price
                $get_price = $book->getBookPrice($request->input('name'));

                //calculate total price
                $total_price = $request->input('quantity') * $get_price;

                $order = Order::create([
                    'user_id' => $currentUser->id,
                    'book_id' => $get_bookid,
                    'address_id' => $get_addressid,
                    'order_id' => $this->generateUniqueOrderId(),
                ]);
                $userId = User::where('id', $currentUser->id)->first();

                $delay = now()->addSeconds(5);
                $userId->notify((new PlaceOrder($order->order_id, $get_BookName, $get_BookAuthor, $request->input('quantity'), $total_price))->delay($delay));
                $book = new Book();
                $book = Book::find($get_bookid);
                $book->quantity -= $request->quantity;
                $book->save();
                return response()->json([
                    'message' => 'Order Successfully Placed...',
                    'OrderId' => $order->order_id,
                    'Book Quantity' => $request->input('quantity'),
                    'Total_Price' => $total_price,
                    'message1' => 'Mail also sent to the user with all details',
                ], 201);
                $value = Cache::remember('orders', 3600, function () {
                    return DB::table('orders')->get();
                });
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
        return response()->json([
            'message' => 'Failed to place order...',
        ], 404);
    }

    //generate unique orderId... for unique order id
    public function generateUniqueOrderId()
    {
        do {
            $orderid = random_int(100000, 999999);
        } while (Order::where("order_id", "=", $orderid)->first());

        return $orderid;
    }

}
