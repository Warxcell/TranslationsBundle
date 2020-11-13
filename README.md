# TranslationBundle

Import your translations into database.

## Installation: 
###### it is recommented to install X.Y.* version - This project follow <a target="_blank" href="https://semver.org/">semver</a> - Patch versions will be always compatible with each other. Minor versions may contain minor BC-breaks.
- composer require object.bg/translationbundle
- Register bundle in AppKernel.php: `new Arxy\TranslationBundle\ArxyTranslationBundle()`

And you are ready to translate. This bundle contains following entities:

`Arxy\TranslationBundle\Entity\TranslationToken` -> `translation_tokens`
`Arxy\TranslationBundle\Entity\Translation` -> `translations`

If you need to edit translations from admin: you can use SonataAdmin. Bundle will register admins automatically if detects that SonataAdmin is installed.

For Object (Entity) Translations see: <a href="https://github.com/vm5/EntityTranslationsBundle" target="_blank">VM5 EntityTranslationsBundle</a>

## For initial import of all tokens:
###### ($locale does not matter since tokens are same for all locales and they are stored in single table, but it's required by Symfony)

php app/console translation:update --output-format="db" $locale $bundle --force