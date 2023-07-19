<?php

use App\Entity\Parcel;
use App\Entity\CustomerOrder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class getOneTest extends WebTestCase
{
    private $client;

    // prépare l'env de test
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
        $customerOrder->setcreatedAt(new \DateTimeImmutable('2023-07-19 12:34:56'));
        $customerOrder->setupdatedAt(new \DateTimeImmutable('2023-07-19 12:34:56'));

        $parcel = new Parcel();
        $parcel->setTrackingNumber(12345);
        $parcel->setWeight(500);
        $parcel->setCustomerOrder($customerOrder);

        

        // Persiste et sauvegarde l'objet en base de données
        $entityManager->persist($customerOrder);
        $entityManager->flush();
    }

    // envoie la requête GET
    public function testFetchApi(): void
    {
        $response = $this->client->request(
            'GET',
            // On suppose qu'un CustomerOrder avec l'id 1 existe en base de données
            "https://127.0.0.1:8000/api/customer_orders/1"
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        // Récupère les données de la réponse HTTP et les décode en tant que tableau
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $statusCode);  // Statut HTTP pour une requête GET réussie doit être 200
        $this->assertEquals('application/ld+json; charset=utf-8', $contentType);

        // Vérifie que les données de la réponse sont celles qu'on attend'
        $this->assertEquals(1, $data['id']);
        $expectedStatusValues = ['completed', 'cancelled', 'completed'];
        $this->assertTrue(is_string($data['status']));
        $this->assertTrue(is_string($data['lastName']));
        $this->assertTrue(is_string($data['firstName']));
        $this->assertTrue(is_string($data['addressLine1']));
        $this->assertTrue(is_string($data['city']));
        $this->assertTrue(is_int($data['postalCode']));
        $this->assertTrue(is_string($data['createdAt']));
        // $this->assertTrue(isset($data['updatedAt']) && (is_string($data['updatedAt']) || is_null($data['updatedAt'])));        
        // Parcel
        $this->assertTrue(is_array($data['parcels']));
        foreach ($data['parcels'] as $parcel) {
            $this->assertTrue(is_int($parcel['trackingNumber']));
            $this->assertTrue(is_int($parcel['weight']));
        }        
    }

    protected function tearDown(): void
    {
        // Récupère l'EntityManager
        $entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();
    
        // Récupère l'instance de CustomerOrder
        $customerOrder = $entityManager->getRepository(CustomerOrder::class)->findOneBy(['orderNumber' => 45678]);
    
        // Récupère les entités Parcel qui font référence à cette CustomerOrder
        $parcels = $entityManager->getRepository(Parcel::class)->findBy(['customerOrder' => $customerOrder]);
    
        // Parcoure et supprime chaque Parcel
        foreach ($parcels as $parcel) {
            $entityManager->remove($parcel);
        }
    
        // Supprime l'instance de la base de données
        $entityManager->remove($customerOrder);
        $entityManager->flush();
    
        parent::tearDown();
    }
}
