JSON LD Dumper / Serializer
===========================

This project aims to help you serialize your objects or other stored static
data into JSON-LD microdata.

Here is a quite large example showing most features:

```php
<?php

use Isometriks\JsonLdDumper\Dumper;
use Isometriks\JsonLdDumper\MappingConfiguration;
use Isometriks\JsonLdDumper\Parser;
use Isometriks\JsonLdDumper\Replacer\DateReplacer;
use Isometriks\JsonLdDumper\Replacer\ExpressionReplacer;
use Isometriks\JsonLdDumper\Replacer\ResourceReplacer;
use Isometriks\JsonLdDumper\Replacer\StaticReplacer;
use Isometriks\JsonLdDumper\Test\Model\AuthorInterface;
use Isometriks\JsonLdDumper\Test\Model\Image;
use Isometriks\JsonLdDumper\Test\Model\NewsArticle;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

include __DIR__ . '/vendor/autoload.php';

$static = [
    'logo' => [
        '@context' => 'http://schema.org/',
        '@type' => 'ImageObject',
        'url' => 'https://riwebgurus.com/media/image/logo.png',
    ],

    'company' => [
        '@context' => 'http://schema.org/',
        '@type' => 'Organization',
        'name' => 'RI Web Gurus',
        'logo' => '$static.logo',
        'url' => 'https://riwebgurus.com',
    ],
];

// Entities
$entities = [
    NewsArticle::class => [
        '@context' => 'http://schema.org/',
        '@type' => 'NewsArticle',
        'headline' => '$resource.headline',
        'image' => '$resource.image',
        'publisher' => '$static.company',
        'datePublished' => '$resource.published',
        'author' => '$resource.author',
    ],

    Image::class => [
        '@context' => 'http://schema.org/',
        '@type' => 'ImageObject',
        'url' => '$resource.url',
        'width' => '$resource.width',
        'height' => '$resource.height',
    ],

    AuthorInterface::class => [
        '@context' => 'http://schema.org/',
        '@type' => 'Person',
        'name' => 'expr:"Mr. " ~ context.getName()',
    ],
];

$mapping = new MappingConfiguration($static, $entities);
$expressionLanguage = new ExpressionLanguage();

$parser = new Parser($mapping, [
    new StaticReplacer($mapping),
    new ResourceReplacer(),
    new DateReplacer(),
    new ExpressionReplacer($expressionLanguage),
]);

$dumper = new Dumper($parser);

echo $dumper->dump([
    new NewsArticle(),
    '$static.company',
]);
```

And the result:

```html
<script type="application/ld+json">
[
    {
        "@context": "http://schema.org/",
        "@type": "NewsArticle",
        "headline": "Here is a headline",
        "image": {
            "@context": "http://schema.org/",
            "@type": "ImageObject",
            "url": "http://placehold.it/800x800",
            "width": 800,
            "height": 800
        },
        "publisher": {
            "@context": "http://schema.org/",
            "@type": "Organization",
            "name": "RI Web Gurus",
            "logo": {
                "@context": "http://schema.org/",
                "@type": "ImageObject",
                "url": "https://riwebgurus.com/media/image/logo.png"
            },
            "url": "https://riwebgurus.com"
        },
        "datePublished": "2016-11-30T03:53:00+00:00",
        "author": {
            "@context": "http://schema.org/",
            "@type": "Person",
            "name": "Mr. Craig Blanchette"
        }
    },
    {
        "@context": "http://schema.org/",
        "@type": "Organization",
        "name": "RI Web Gurus",
        "logo": {
            "@context": "http://schema.org/",
            "@type": "ImageObject",
            "url": "https://riwebgurus.com/media/image/logo.png"
        },
        "url": "https://riwebgurus.com"
    }
]
</script>
```

Safe Values
-----------

When serializing any of the entities / static mappings, there can be value
replacements. Some are deemed safe (replacing an object, that then can be also
serialized) and some that could be dangerous (replacing a string from an object).

If the string replacement from an object contains any of the patterns that we
replace then we don't want to parse that value any further. A possible not very
harmful example:

```php
class FakeModel
{
    public $var = '$static.company';
}
```

If any of the objects are user-created then it could be possible to obtain
information that shouldn't be returned. Since you can create your own parsers
you might want to be careful with this especially if you provide a callback
parser, or with the Symfony implementation calling arbitrary services. These
should only be allowed from the mappings themselves, not from the return values
of objects.