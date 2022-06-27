<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\MyComponent;
use Doctrine\ORM\QueryBuilder;

class MyComponentRepository extends AbstractRepository
{
    public function findAllForDataGrid(): QueryBuilder
    {
        return $this->findAll();
    }
}
