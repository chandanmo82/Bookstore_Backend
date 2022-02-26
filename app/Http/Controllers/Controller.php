<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Swagger(
 *   basePath="",
 *   schemes= {"http", "https"},
 *   host = L5_SWAGGER_CONST_HOST,
 *   @OA\Info(
 *     version="1.0.0",
 *     title="Swagger Integration with BookStore Application",
 *     description="Integrate Swagger in Laravel  BookStoreapplication",
 *   @OA\Contact(
 *          email="chandanmohanty11111@gmail.com"
 *     ),
 *   )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
