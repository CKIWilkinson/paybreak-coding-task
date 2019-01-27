<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FraudIdentification extends Controller
{
    public function fraudidentification(Request $request)
    {
        $threshold = $request->threshold;
        $applications = $request->applications;
        if (
          is_null($threshold) ||
          !is_numeric($threshold)
        ) {
          return response()->json('threshold must exist and be numeric', 400);
        }
        if (
          empty($applications)
        ) {
          return response()->json('applications must exist and be in an array', 400);
        }
        return response()->json([], 200);
    }
}
