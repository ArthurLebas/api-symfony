<?php

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class postTest extends WebTestCase
{

    private $client;

    // Prépare l'env de test
    protected function setUp(): void
    {
        $this->client = HttpClient::create(['verify_peer' => false]);
    }

    public function testFetchApi(): void
    {
        $response = $this->client->request(
            'POST',
            'https://127.0.0.1:8000/api/customer_orders',
            [
                'json' => [
                    'status' => 'created',
                    'orderNumber' => 12345,
                    'lastName' => 'Doe',
                    'firstName' => 'John',
                    'addressLine1' => '123 Street',
                    'city' => 'Paris',
                    'postalCode' => 75000,
                    'createdAt' => (new \DateTimeImmutable())->format(\DateTime::ATOM),
                    'parcels' => [
                        [
                            'trackingNumber' => 123456789,
                            'weight' => 2000,
                            'productCodes' => ['ABC123', 'DEF456'],
                        ],
                    ],
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        // Récupère les données de la réponse HTTP et les décode en tant que tableau
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(201, $statusCode);
        $this->assertEquals('application/ld+json; charset=utf-8', $contentType);

        $this->assertEquals('created', $data['status']);
        $this->assertEquals(12345, $data['orderNumber']);
        $this->assertEquals('Doe', $data['lastName']);
        $this->assertEquals('John', $data['firstName']);
        $this->assertEquals('123 Street', $data['addressLine1']);
        $this->assertEquals('Paris', $data['city']);
        $this->assertEquals(75000, $data['postalCode']);

        $this->assertIsArray($data['parcels']);
        $this->assertNotEmpty($data['parcels']);

        $firstParcel = $data['parcels'][0]; 

        $this->assertEquals(123456789, $firstParcel['trackingNumber']);
        $this->assertEquals(2000, $firstParcel['weight']);
        $this->assertIsArray($firstParcel['productCodes']);
        $this->assertContains('ABC123', $firstParcel['productCodes']);
        $this->assertContains('DEF456', $firstParcel['productCodes']);

    }
}
