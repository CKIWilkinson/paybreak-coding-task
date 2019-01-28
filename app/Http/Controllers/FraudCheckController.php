<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FraudCheck extends Controller
{
    /*
      POST /api/fraudcheck
      Expected body:
      {
        "threshold": 00.00,
        "applications": [
          "7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00"
        ]
      }

      Applications are fraudulent if they exceed the given threshold within
      24 hours from a single postcode. Applications are a comma 
      separated list of a hashed postal code, a timestamp and an amount, all in
      the format shown above.

      returns an array of postcodes found to have made fraudulent applications IE:
      ["9976bdc9565c7a2e34d432a2e5778bf8fa7eb735", "efa0bee4e432d97b72d17462c71018063ecb9f0e"]
    */
    public function fraudcheck(Request $request)
    {
        $threshold = $request->threshold;
        $applications = $request->applications;

        // Validates that we have the necessary parameters
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
        $fraudulentPostcodes = [];
        $processedApplications = [];
        foreach ($applications as $application) {
          // split the application string up into its components
          $application = explode(', ', $application);

          $postcode = $application[0];
          if (in_array($postcode, $fraudulentPostcodes, true)) {
            // We already know this postcode is fraudulent, no reason to process it again
            continue;
          }

          $amount = $application[2];
          if ($amount > $threshold) {
            $fraudulentPostcodes[] = $postcode;
            continue;
          }

          $datetime = new \DateTime($application[1]);

          // If no applications for this postcode have been processed yet then
          // there's nothing to iterate over
          if (empty($processedApplications[$postcode])) {
            $processedApplications[$postcode][] = [
              'datetime' => $datetime,
              'amount' => $amount
            ];
            continue;
          }
          foreach ($processedApplications[$postcode] as $key => $processedApplication) {
            // Checks the difference between the datetimes of the 2 applications
            // and outputs it as whole days, so if it's within 24 hours it will return 0
            // and if it's more than 24 hours it will return an integer of one or above,
            // which is truthy.
            if ($datetime->diff($processedApplication['datetime'])->format('%a')) {
              unset($processedApplications[$postcode][$key]);
              continue;
            }
            $processedApplications[$postcode][$key]['amount'] = $processedApplication['amount'] + $amount;
            if ($processedApplications[$postcode][$key]['amount'] > $threshold) {
              $fraudulentPostcodes[] = $postcode;
              // skips to the next iteration over the applications array because
              // we know that this postcode is fraudulent
              continue(2);
            }
          }
          $processedApplications[$postcode][] = [
            'datetime' => $datetime,
            'amount' => $amount
          ];

        }
        return response()->json($fraudulentPostcodes, 200);
    }
}
