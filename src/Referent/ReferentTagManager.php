<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\ReferentTagRepository;

class ReferentTagManager
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    public function assignAdherentLocalTag(Adherent $adherent): void
    {
        $adherent->removeReferentTags();

        foreach (ManagedAreaUtils::getCodesFromAdherent($adherent) as $tagCode) {
            if (!$tag = $this->referentTagRepository->findOneByCode($tagCode)) {
                continue;
            }

            $adherent->addReferentTag($tag);
        }
    }
}
