<?php

namespace ObjectBG\TranslationBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetaData,
    Doctrine\ORM\Query\Filter\SQLFilter;

class LanguageFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->getName() != 'ObjectBG\TranslationBundle\Entity\Language') {
            return "";
        }
        return $targetTableAlias . '.locale = ' . $this->getParameter('locale');
    }
}
