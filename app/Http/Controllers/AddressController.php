<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BookStoreAppException;

class AddressController extends Controller
{
    /**
     * This method will take input address,city,state,landmark,pincode and addresstype from user 
     * and will store in the database for the respective user
     */
    /**
     * @OA\Post(
     *   path="/api/auth/addaddress",
     *   summary="Add Address",
     *   description="User Can Add Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address","city","state","landmark", "pincode", "addresstype"},
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="city", type="string"),              
     *               @OA\Property(property="state", type="string"),
     *               @OA\Property(property="landmark", type="string"),
     *               @OA\Property(property="pincode", type="string"),
     *               @OA\Property(property="addresstype", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Address Added Successfully"),
     *   @OA\Response(response=401, description="Address alredy present for the user"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * */
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|between:2,600',
            'city' => 'required|string|between:2,100',
            'state' => 'required|string|between:2,100',
            'landmark' => 'required|string|between:2,100',
            'pincode' => 'required|string|between:2,10',
            'addresstype' => 'required|string|between:2,100',
        ]);

        if ($validator->fails()) {
            Log::info('Validator Fails');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $address = new Address;
                $address->user_id = $currentUser->id;
                $address->address = $request->input('address');
                $address->city = $request->input('city');
                $address->state = $request->input('state');
                $address->landmark = $request->input('landmark');
                $address->pincode = $request->input('pincode');
                $address->addresstype = $request->input('addresstype');
                $address->save();
                $value = Cache::remember('addresses', 3600, function () {
                    return DB::table('addresses')->get();
                });
                Log::info('Address Added To Respective User', ['user_id', '=', $currentUser->id]);
                return response()->json([
                    'message' => ' Address Added Successfully'
                ], 201);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This method will take input address,city,state,landmark,pincode,addresstype and where user
     * want to change then can update and will save in database the updated data which updated by 
     * respective user
     */
    /**
     * @OA\Post(
     *   path="/api/auth/updateaddress",
     *   summary="Update Address",
     *   description="User Can Update Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address","city","state","landmark", "pincode", "addresstype"},
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="city", type="string"),              
     *               @OA\Property(property="state", type="string"),
     *               @OA\Property(property="landmark", type="string"),
     *               @OA\Property(property="pincode", type="string"),
     *               @OA\Property(property="addresstype", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Address Updated Successfully"),
     *   @OA\Response(response=401, description="Address not present add address first"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     * */
    public function updateAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|between:2,600',
            'city' => 'required|string|between:2,100',
            'state' => 'required|string|between:2,100',
            'landmark' => 'required|string|between:2,100',
            'pincode' => 'required|string|between:2,10',
            'addresstype' => 'required|string|between:2,100',
        ]);

        if ($validator->fails()) {
            Log::info('Validator Fails');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $address_exist = Address::select('address')->where([
                    ['user_id', '=', $currentUser->id]
                ])->get();

                if (count($address_exist) == 0) {
                    Log::error('Address is empty');
                    throw new BookStoreAppException("Address not present add address first", 401);
                }

                $address = Address::where('user_id', $currentUser->id)->first();
                $address->fill($request->all());
                $value = Cache::remember('addresses', 3600, function () {
                    return DB::table('addresses')->get();
                });
                if ($address->save()) {
                    Log::info('Address Updated For Respective User', ['user_id', '=', $currentUser->id]);
                    return response()->json([
                        'message' => ' Address Updated Successfully'
                    ], 201);
                }
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }

    /**
     * This method will take input from user as userId and will delete the address present for
     * the respective user in database
     */
    /**
     * @OA\Post(
     *   path="/api/auth/deleteaddress",
     *   summary="Delete Address",
     *   description=" Delete Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"user_id"},
     *               @OA\Property(property="user_id", type="integer"),
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
    public function deleteAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            Log::info('Validator Fails');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $user_id = $request->input('user_id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = $currentUser->addresses()->find($user_id);

            if (!$user) {
                return response()->json(['message' => 'User not Found'], 404);
            }

            if ($user->delete()) {
                Log::info('Address Deleted For Respective User', ['user_id', '=', $currentUser->id]);
                return response()->json(['message' => 'Address deleted Sucessfully'], 201);
            } else {
                throw new BookStoreAppException("Invalid authorization token", 404);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
    /**
     * This method will authenticate the user and will return all the address of respective user
     */
    /**
     * @OA\Get(
     *   path="/api/auth/getaddess",
     *   summary="Get address ",
     *   description=" Get Address ",
     *   @OA\RequestBody(
     *         
     *    ),
     *   @OA\Response(response=404, description="Address not found"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function getAddress()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        try {
            if ($currentUser) {
                $address = new Address();
                $user_id = $currentUser->id;
                $user = $address->getUserAddress($user_id);
                if ($user == '[]') {
                    throw new BookStoreAppException("Address not found", 404);
                }
                Log::info('Address fetched For Respective User', ['user_id', '=', $currentUser->id]);
                return response()->json([
                    'address' => $user,
                    'message' => 'Fetched Address Successfully'
                ], 201);
            }
        } catch (BookStoreAppException $e) {
            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }
    }
}
