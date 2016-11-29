JSON LD Dumper / Serializer
===========================

This project aims to help you serialize your objects or other stored static
data into JSON-LD microdata.

Here is a quite large example showing most features:

```php
<?php

use Isometriks\JsonLdDumper\Dumper;
use Isometriks\JsonLdDumper\MappingConfiguration;
use Isometriks\JsonLdDumper\Parser\DateParser;
use Isometriks\JsonLdDumper\Parser\Parser;
use Isometriks\JsonLdDumper\Parser\ResourceParser;
use Isometriks\JsonLdDumper\Parser\StaticParser;
use Isometriks\JsonLdDumper\Test\Model\AuthorInterface;
use Isometriks\JsonLdDumper\Test\Model\Image;
use Isometriks\JsonLdDumper\Test\Model\NewsArticle;

include __DIR__ . '/vendor/autoload.php';

$static = array(
    'logo' => array(
        '@context' => 'http://schema.org/',
        '@type' => 'ImageObject',
        'url' => 'https://riwebgurus.com/media/image/logo.png',
    ),

    'company' => array(
        '@context' => 'http://schema.org/',
        '@type' => 'Organization',
        'name' => 'RI Web Gurus',
        'logo' => '$static.logo',
        'url' => 'https://riwebgurus.com',
    ),
);

// Entities
$entities = array(
    NewsArticle::class => array(
        '@context' => 'http://schema.org/',
        '@type' => 'NewsArticle',
        'headline' => '$resource.headline',
        'image' => '$resource.image',
        'publisher' => '$static.company',
        'datePublished' => '$resource.published',
        'author' => '$resource.author',
    ),

    Image::class => array(
        '@context' => 'http://schema.org/',
        '@type' => 'ImageObject',
        'url' => '$resource.url',
        'width' => '$resource.width',
        'height' => '$resource.height',
    ),

    AuthorInterface::class => array(
        '@context' => 'http://schema.org/',
        '@type' => 'Person',
        'name' => '$resource.name',
    ),
);

$mapping = new MappingConfiguration($static, $entities);

$parser = new Parser($mapping, array(
    new StaticParser($mapping),
    new ResourceParser(),
    new DateParser(),
));

$dumper = new Dumper($mapping, $parser);

echo $dumper->dumpArray(array(
    new NewsArticle(),
    'company',
));
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
        "datePublished": "2016-11-29T19:40:17+00:00",
        "author": {
            "@context": "http://schema.org/",
            "@type": "Person",
            "name": "Craig Blanchette"
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
