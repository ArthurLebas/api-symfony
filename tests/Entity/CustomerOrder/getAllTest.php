<?php

use App\Entity\Parcel;
use App\Entity\CustomerOrder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class getAllTest extends WebTestCase
{
    private $client;

    // Prépare l'env de test
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = HttpClient::create(['verify_peer' => false]);

        // Récupère l'EntityManager
        $entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();

        // Crée plusieurs nouvelles instances de CustomerOrder pour le test
        for ($i = 0; $i < 3; $i++) {
            $customerOrder = new CustomerOrder();
            $customerOrder->setStatus('cancelled');
            $customerOrder->setOrderNumber(45678 + $i);
            $customerOrder->setlastName('Doe' . $i);
            $customerOrder->setfirstName('John' . $i);
            $customerOrder->setaddressLine1('123 Street');
            $customerOrder->setcity('Paris');
            $customerOrder->setpostalCode(75000);
            $customerOrder->setcreatedAt(new \DateTimeImmutable('2023-07-19 12:34:56'));

            $parcel = new Parcel();
            $parcel->setTrackingNumber(12345 + $i);
            $parcel->setWeight(500);
            $parcel->setCustomerOrder($customerOrder);

            // Persiste et sauvegarde l'objet en base de données
            $entityManager->persist($customerOrder);
        }
        $entityManager->flush();
    }

    public function testFetchApi(): void
    {
        $response = $this->client->request(
            'GET',
            'https://127.0.0.1:8000/api/customer_orders'
        );
    
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        // Récupère les données de la réponse HTTP et les décode en tant que tableau
        $data = json_decode($response->getContent(), true);
    
        $this->assertEquals(200, $statusCode);
        $this->assertEquals('application/ld+json; charset=utf-8', $contentType);
    
        // Vérifie qu'on a bien reçu un tableau d'objets CustomerOrder
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    
        // Vérifie que les métadonnées hydra sont présentes
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertArrayHasKey('hydra:totalItems', $data);
        $this->assertArrayHasKey('hydra:search', $data);
    
        // Vérifie que hydra:member est un tableau
        $this->assertIsArray($data['hydra:member']);
        $this->assertNotEmpty($data['hydra:member']);
    
        // Vérifie que chaque CustomerOrder a le format de données attendu
        foreach ($data['hydra:member'] as $customerOrderData) {
            $this->assertIsArray($customerOrderData);
            $this->assertArrayHasKey('id', $customerOrderData);
            $this->assertArrayHasKey('status', $customerOrderData);
            $this->assertArrayHasKey('orderNumber', $customerOrderData);
            $this->assertArrayHasKey('lastName', $customerOrderData);
            $this->assertArrayHasKey('firstName', $customerOrderData);
            $this->assertArrayHasKey('addressLine1', $customerOrderData);
            $this->assertArrayHasKey('city', $customerOrderData);
            $this->assertArrayHasKey('postalCode', $customerOrderData);
            $this->assertArrayHasKey('createdAt', $customerOrderData);
            $this->assertArrayHasKey('parcels', $customerOrderData);
    
            // Vérifie que les parcels ont le bon format
            foreach ($customerOrderData['parcels'] as $parcel) {
                $this->assertIsArray($parcel);
                $this->assertArrayHasKey('trackingNumber', $parcel);
                $this->assertArrayHasKey('weight', $parcel);
                $this->assertArrayHasKey('productCodes', $parcel);
                $this->assertArrayHasKey('customerOrder', $parcel);
            }
        }
    }
    

    protected function tearDown(): void
    {
        // Récupère l'EntityManager
        $entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();
    
        // Récupère l'instance de CustomerOrder
        for ($i = 0; $i < 3; $i++) {
            $customerOrder = $entityManager->getRepository(CustomerOrder::class)->findOneBy(['orderNumber' => 45678 + $i]);
        
            // Récupère les entités Parcel qui font référence à cette CustomerOrder
            $parcels = $entityManager->getRepository(Parcel::class)->findBy(['customerOrder' => $customerOrder]);
        
            // Parcoure et supprime chaque Parcel
            foreach ($parcels as $parcel) {
                $entityManager->remove($parcel);
            }
        
            // Supprime l'instance de la base de données
            $entityManager->remove($customerOrder);
        }
        $entityManager->flush();
    
        parent::tearDown();
    }
}
