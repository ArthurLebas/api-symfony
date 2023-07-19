<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\ParcelRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParcelRepository::class)]
#[
    ApiResource(
        operations: [],
        // itemOperations: {},
        normalizationContext: ['groups' => ['parcel:read']],
        denormalizationContext: ['groups' => ['parcel:create']],
    ),
    ApiFilter(SearchFilter::class, properties: ['createdAt' => 'partial']),
    ApiFilter(OrderFilter::class, properties: ['createdAt' => 'desc']),
]
class Parcel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // si on veux récupérer l'id avec CustomerOrder on dois rajouter par exemple 'customerOrder:read'
    #[Groups(['parcel:read', 'parcel:create'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['parcel:read', 'parcel:create', 'customerOrder:read', 'customerOrder:create'])]
    private ?int $trackingNumber = null;

    #[ORM\Column]
    #[Groups(['parcel:read', 'parcel:create', 'customerOrder:read', 'customerOrder:create'])]
    private ?int $weight = null;

    #[ORM\Column]
    #[Groups(['parcel:read', 'parcel:create', 'customerOrder:read', 'customerOrder:create'])]
    private array $productCodes = [];

    // #[ORM\ManyToOne(inversedBy: 'Parcels')]
    // #[Groups(['parcel:read'])]
    // private ?CustomerOrder $customerOrder = null;

    // #[ORM\ManyToOne(targetEntity: CustomerOrder::class, inversedBy: 'parcels')]
    // #[Groups(['parcel:read', 'parcel:create', 'customerOrder:read', 'customerOrder:create'])]
    // private ?CustomerOrder $customerOrder = null;

    #[ORM\ManyToOne(targetEntity: CustomerOrder::class, inversedBy: 'parcels')]
    #[Groups(['parcel:read', 'parcel:create', 'customerOrder:read', 'customerOrder:write'])]
    private ?CustomerOrder $customerOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrackingNumber(): ?int
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(int $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getProductCodes(): array
    {
        return $this->productCodes;
    }

    public function setProductCodes(array $productCodes): static
    {
        $this->productCodes = $productCodes;

        return $this;
    }

    public function getCustomerOrder(): ?CustomerOrder
    {
        return $this->customerOrder;
    }

    // public function setCustomerOrder(?CustomerOrder $customerOrder): static
    // {
    //     $this->customerOrder = $customerOrder;

    //     return $this;
    // }

    public function setCustomerOrder(?CustomerOrder $customerOrder): self
    {
        // unset the owning side of the relation if necessary
        if ($customerOrder === null && $this->customerOrder !== null) {
            $this->customerOrder->removeParcel($this);
        }

        // set the owning side of the relation if necessary
        if ($customerOrder !== null && !$customerOrder->getParcels()->contains($this)) {
            $customerOrder->addParcel($this);
        }

        $this->customerOrder = $customerOrder;

        return $this;
    }
}
