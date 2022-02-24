<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

   /**
     * @OA\OpenApi(
     *     @OA\Info(
     *         version="1.0",
     *         title="BookStore Application",
     *         description="BookStore Application ",
     *     )
     * )
     */
class Controller extends BaseController
{
 
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
