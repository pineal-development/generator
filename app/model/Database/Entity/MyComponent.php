<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TDeletedAt;
use App\Model\Database\Entity\Attributes\TEntity2Array;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MyComponentRepository")
 * @ORM\Table(name="`myComponent`")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class MyComponent extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;
    use TDeletedAt;
    use TEntity2Array;
}
