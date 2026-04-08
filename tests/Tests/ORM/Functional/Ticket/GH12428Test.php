<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;
use PHPUnit\Framework\Attributes\Group;

/** @see https://github.com/doctrine/orm/issues/12428 */
#[Group('GH12428')]
class GH12428Test extends OrmFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchemaForModels(
            GH12428BusinessCase::class,
            GH12428Contract::class,
            GH12428Attachment::class,
        );
    }

    public function testIssue(): void
    {
        $businessCase = new GH12428ABusinessCase();
        $attachment   = new GH12428Attachment($businessCase->contract);

        $this->_em->persist($businessCase);
        $this->_em->persist($attachment);
        $this->_em->flush();
        $this->_em->clear();

        $loadedAttachment = $this->_em->find(GH12428Attachment::class, $attachment->id);
        self::assertNotNull($loadedAttachment);
    }
}

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorMap([
    'A' => GH12428ABusinessCase::class,
])]
abstract class GH12428BusinessCase
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public int|null $id = null;

    #[ORM\OneToOne(mappedBy: 'id', cascade: ['persist'])]
    public GH12428Contract $contract;

    public function __construct()
    {
        $this->contract = new GH12428Contract($this);
    }
}

#[ORM\Entity]
class GH12428ABusinessCase extends GH12428BusinessCase
{
}

#[ORM\Entity]
class GH12428Attachment
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public int|null $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        public GH12428Contract $contract,
    ) {
    }
}

#[ORM\Entity]
class GH12428Contract
{
    public function __construct(
        #[ORM\Id]
        #[ORM\OneToOne(inversedBy: 'contract')]
        #[ORM\JoinColumn(name: 'id')]
        public GH12428BusinessCase $id,
    ) {
    }
}
