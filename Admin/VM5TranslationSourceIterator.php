<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ObjectBG\TranslationBundle\Admin;

use Doctrine\ORM\Query;
use Exporter\Exception\InvalidMethodCallException;
use ObjectBG\TranslationBundle\Entity\Language;

/**
 * Description of DoctrineORMQuerySourceIterator
 *
 * @author Zuza
 */
class VM5TranslationSourceIterator extends \Exporter\Source\DoctrineORMQuerySourceIterator
{
    /**
     * @var Language[]
     */
    protected $languages;

    /**
     * VM5TranslationSourceIterator constructor.
     * @param Query $query
     * @param array $languages
     */
    public function __construct(Query $query, array $languages)
    {
        $this->query = clone $query;
        $this->query->setParameters($query->getParameters());
        foreach ($query->getHints() as $name => $value) {
            $this->query->setHint($name, $value);
        }

        $this->languages = $languages;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $tokens = $this->iterator->current();
        $field = [];
        foreach ($tokens as $token) {
            $field['id'] = $token->getId();
            $field['token'] = $token->getToken();
            $field['catalogue'] = $token->getCatalogue();

            foreach ($this->languages as $lang) {
                $translation = $token->getTranslation($lang);
                if ($translation != null) {
                    $field[$lang->getName()] = $translation->getTranslation();
                } else {
                    $field[$lang->getName()] = null;
                }
            }
        }

        $this->query->getEntityManager()->getUnitOfWork()->detach($token);

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if ($this->iterator) {
            throw new InvalidMethodCallException('Cannot rewind a Doctrine\ORM\Query');
        }

        $this->iterator = $this->query->iterate();
        $this->iterator->rewind();
    }
}
