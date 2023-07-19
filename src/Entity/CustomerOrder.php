<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CustomerOrderRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: CustomerOrderRepository::class)]
#[UniqueEntity("orderNumber")]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(validationContext: ['groups' => 'customerOrder:create'], denormalizationContext: ['groups' => 'customerOrder:create', 'parcel:create']),
        new Put(validationContext: ['groups' => ['customerOrder:upDate', 'parcel:create']]),
    ],
    normalizationContext: ['groups' => ['customerOrder:read']],
    denormalizationContext: ['groups' => ['customerOrder:upDate']],
),
    // permet de choisir la manière de récupérer les entitées, ici par createdAt
    ApiFilter(SearchFilter::class, properties: ['createdAt' => 'partial']),
    ApiFilter(OrderFilter::class, properties: ['createdAt' => 'desc']),
]
class CustomerOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // permet de rendre id visible en lecture
    #[Groups(['customerOrder:read'])]
    private ?int $id = null;
    
    public const STATUS = ['created', 'cancelled', 'completed'];

    #[ORM\Column(length: 10)]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    #[Assert\Choice(choices: CustomerOrder::STATUS, message: 'Invalid value for status', groups: ['customerOrder:create'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    private ?int $orderNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customerOrder:read', 'customerOrder:create', 'customerOrder:upDate', 'parcel:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customerOrder:read', 'customerOrder:create', 'customerOrder:upDate', 'parcel:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    private ?string $city = null;

    #[ORM\Column]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    private ?int $postalCode = null;

    #[ORM\Column]
    #[Groups(['customerOrder:read', 'customerOrder:create'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['customerOrder:read', 'customerOrder:upDate'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'customerOrder', targetEntity: Parcel::class, cascade: ['persist'])]
    #[Groups(['customerOrder:read', 'customerOrder:create', "parcel: create"])]
    private Collection $parcels;

    public function __construct()
    {
        $this->parcels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps(): void
    {
        // Change la valeur de updatedAt dès que l'on modifie l'entitée
        $this->setUpdatedAt(new \DateTimeImmutable());
    }

    /**
     * @return Collection<int, Parcel>
     */
    public function getParcels(): Collection
    {
        return $this->parcels;
    }

    public function addParcel(Parcel $parcel): static
    {
        if (!$this->parcels->contains($parcel)) {
            $this->parcels->add($parcel);
            $parcel->setCustomerOrder($this);
        }

        return $this;
    }

    public function removeParcel(Parcel $parcel): static
    {
        if ($this->parcels->removeElement($parcel)) {
            // set the owning side to null (unless already changed)
            if ($parcel->getCustomerOrder() === $this) {
                $parcel->setCustomerOrder(null);
            }
        }

        return $this;
    }

}
