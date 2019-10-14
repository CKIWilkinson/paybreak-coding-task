<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FraudCheckControllerTest extends TestCase
{
    /**
     * Endpoint should return appropriate error when not given a threshold
     *
     * @return void
     */
    public function testFraudCheckNoThreshold(): void
    {
      $response = $this->call('POST', 'api/fraudcheck', ["applications" => ["7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00"]]);

      $response->assertStatus(400);
      $this->assertEquals('"threshold must exist and be numeric"', $response->getContent());
    }

    /**
     * Endpoint should return appropriate error when given an invalid threshold
     *
     * @return void
     */
    public function testFraudCheckInvalidThreshold(): void
    {
      $response = $this->call('POST', 'api/fraudcheck', ["threshold" => "invalid", "applications" => ["7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00"]]);

      $response->assertStatus(400);
      $this->assertEquals('"threshold must exist and be numeric"', $response->getContent());
    }

    /**
     * Endpoint should return appropriate error when given no applications
     *
     * @return void
     */
    public function testFraudCheckNoApplications(): void
    {
      $response = $this->call('POST', 'api/fraudcheck', ["threshold" => 100]);

      $response->assertStatus(400);
      $this->assertEquals('"applications must exist and be in an array"', $response->getContent());
    }
    /**
     * Endpoint should return appropriate error when given an empty applications array
     *
     * @return void
     */
    public function testFraudCheckEmptyApplications(): void
    {
      $response = $this->call('POST', 'api/fraudcheck', ["threshold" => 100, "applications" => []]);

      $response->assertStatus(400);
      $this->assertEquals('"applications must exist and be in an array"', $response->getContent());
    }
    /**
     * Endpoint shouldn't return any postcodes when none are fraudulent
     *
     * @return void
     */
    public function testFraudCheckNoFraudulentApplications(): void
    {
      $response = $this->call('POST', 'api/fraudcheck', ["threshold" => 100, "applications" => ["7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00"]]);

      $response->assertStatus(200);
      $this->assertEquals('[]', $response->getContent());
    }
    /**
     * Endpoint should return fraudulent postcodes when there are some
     *
     * @return void
     */
    public function testFraudCheckHasFraudulentApplications(): void
    {
      /*
      The POST data prettified in JSON format:
      {
      	"threshold": 150.75,
      	"applications": [
      		"7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00",
      		"9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T15:12:11, 60.00",
      		"9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T16:12:11, 60.00",
      		"9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T16:30:05, 60.00",
      		"9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T17:12:11, 60.00",
      		"d958d5f1b2976086d991b0cfe4e7b23a5b2fa408, 2019-01-29T18:04:20, 75.40",
      		"7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T18:12:11, 40.00",
      		"7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-30T13:12:10, 10.00",
      		"d958d5f1b2976086d991b0cfe4e7b23a5b2fa408, 2019-01-30T13:12:11, 75.40",
      		"7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-31T18:12:11, 10.00",
      		"efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-01T23:12:11, 60.00",
      		"efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-02T14:50:11, 60.00",
      		"efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-03T18:12:11, 60.00",
      		"efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-04T20:09:40, 150.00",
      		"5fce533699af002a03d0eda90c097b188f345eed, 2019-02-06T12:30:40, 200.00"
      	]
      }
      */
      $response = $this->call('POST', 'api/fraudcheck', ["threshold" => 150.75, "applications" => ["7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T13:12:11, 100.00", "9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T15:12:11, 60.00", "9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T16:12:11, 60.00", "9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T16:30:05, 60.00", "9976bdc9565c7a2e34d432a2e5778bf8fa7eb735, 2019-01-29T17:12:11, 60.00", "d958d5f1b2976086d991b0cfe4e7b23a5b2fa408, 2019-01-29T18:04:20, 75.40", "7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-29T18:12:11, 40.00", "7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-30T13:12:10, 10.00", "d958d5f1b2976086d991b0cfe4e7b23a5b2fa408, 2019-01-30T13:12:11, 75.40", "7a81b904f63762f00d53c4d79825420efd00f5f9, 2019-01-31T18:12:11, 10.00", "efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-01T23:12:11, 60.00", "efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-02T14:50:11, 60.00", "efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-03T18:12:11, 60.00", "efa0bee4e432d97b72d17462c71018063ecb9f0e, 2019-02-04T20:09:40, 150.00", "5fce533699af002a03d0eda90c097b188f345eed, 2019-02-06T12:30:40, 200.00"]]);

      $response->assertStatus(200);
      $this->assertEquals('["9976bdc9565c7a2e34d432a2e5778bf8fa7eb735","d958d5f1b2976086d991b0cfe4e7b23a5b2fa408","5fce533699af002a03d0eda90c097b188f345eed"]', $response->getContent());
    }
}
