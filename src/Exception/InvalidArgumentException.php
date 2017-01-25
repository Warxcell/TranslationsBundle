<?php

namespace ObjectBG\TranslationBundle\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{

    public static function missingTranslations($TranslatableClass)
    {
        return new self('Missing translations association for entity '.$TranslatableClass);
    }

    public static function missingRequiredAnnotation($Class, $Annotation)
    {
        return new self($Class.' is missing required annotation '.$Annotation);
    }
}
