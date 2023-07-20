<?php

use App\Entity\Parcel;
use App\Entity\CustomerOrder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class putTest extends WebTestCase
{

    private $client;

    // Prépare l'env de test
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = HttpClient::create(['verify_peer' => false]);

        // Récupère l'EntityManager
        $entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();

        // Crée une nouvelle instance de CustomerOrder
        $customerOrder = new CustomerOrder();
        $customerOrder->setStatus('cancelled');
        $customerOrder->setOrderNumber(45678);
        $customerOrder->setlastName('Doe');
        $customerOrder->setfirstName('John');
        $customerOrder->setaddressLine1('123 Street');
        $customerOrder->setcity('Paris');
        $customerOrder->setpostalCode(75000);
        $customerOrder->setcreatedAt(new \DateTimeImmutable());
        $customerOrder->setupdatedAt(new \DateTimeImmutable());

        $parcel = new Parcel();
        $parcel->setTrackingNumber(12345);
        $parcel->setWeight(500);
        $parcel->setCustomerOrder($customerOrder);

        // Persiste et sauvegarde l'objet en base de données
        $entityManager->persist($customerOrder);
        $entityManager->flush();
    }

    public function testFetchApi(): void
    {
        $response = $this->client->request(
            'PUT',
            'https://127.0.0.1:8000/api/customer_orders/1',
            [
                'json' => [
                    'lastName' => 'Di',
                    'firstName' => 'Jenh',
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        // Récupère les données de la réponse HTTP et les décode en tant que tableau
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $statusCode);
        $this->assertEquals('application/ld+json; charset=utf-8', $contentType);

        $now = new \DateTimeImmutable();
        //Vérifie que le nom, le prénom et updatedAt ont été correctement mis à jour 
        $this->assertEquals('Di', $data['lastName']);
        $this->assertEquals('Jenh', $data['firstName']);
        $updatedDateTime = new \DateTime($data['updatedAt']);
        $this->assertGreaterThanOrEqual($updatedDateTime->format('Y-m-d\TH:i'),$now->format('Y-m-d\TH:i'),);
        
    }
}
