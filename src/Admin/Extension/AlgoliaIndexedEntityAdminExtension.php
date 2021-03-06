<?php

namespace AppBundle\Admin\Extension;

use AppBundle\Algolia\AlgoliaIndexedEntityManager;
use AppBundle\Entity\AlgoliaIndexedEntityInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class AlgoliaIndexedEntityAdminExtension extends AbstractAdminExtension
{
    private $algoliaManager;

    public function __construct(AlgoliaIndexedEntityManager $manager)
    {
        $this->algoliaManager = $manager;
    }

    /**
     * @param AdminInterface                $admin
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postPersist(AdminInterface $admin, $object)
    {
        $this->algoliaManager->postPersist($object);
    }

    /**
     * @param AdminInterface                $admin
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function postUpdate(AdminInterface $admin, $object)
    {
        $this->algoliaManager->postUpdate($object);
    }

    /**
     * @param AdminInterface                $admin
     * @param AlgoliaIndexedEntityInterface $object
     */
    public function preRemove(AdminInterface $admin, $object)
    {
        $this->algoliaManager->preRemove($object);
    }
}
