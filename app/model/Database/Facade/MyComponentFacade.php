<?php

declare(strict_types=1);

namespace App\Model\Database\Facade;

use App\Model\Database\Entity\MyComponent;
use App\Model\Database\Repository\MyComponentRepository;

final class MyComponentFacade extends AbstractFacade
{
    /** @var MyComponentRepository */
    public MyComponentRepository $myComponentRepository;

    public function injectRepository(): void
    {
        $this->myComponentRepository = $this->entityManager->getRepository(MyComponent::class);
    }

    public function save(MyComponent $myComponent): void
    {
        $this->entityManager->persist($myComponent);
        $this->entityManager->flush();
    }

    public function create(): MyComponent
    {
        return new MyComponent();
    }

    public function remove(MyComponent $entity): bool
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return true;
    }

    public function findAll(): array
    {
        return $this->myComponentRepository->findAll()->getQuery()->getArrayResult();
    }
}
