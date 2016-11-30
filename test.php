<?php

use Isometriks\JsonLdDumper\Dumper;
use Isometriks\JsonLdDumper\MappingConfiguration;
use Isometriks\JsonLdDumper\Parser\DateParser;
use Isometriks\JsonLdDumper\Parser\ExpressionParser;
use Isometriks\JsonLdDumper\Parser\Parser;
use Isometriks\JsonLdDumper\Parser\ResourceParser;
use Isometriks\JsonLdDumper\Parser\StaticParser;
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
        'name' => 'expr:"$static.company"',
    ],
];

$mapping = new MappingConfiguration($static, $entities);
$expressionLanguage = new ExpressionLanguage();

$parser = new Parser($mapping, [
    new StaticParser($mapping),
    new ResourceParser(),
    new DateParser(),
    new ExpressionParser($expressionLanguage),
]);

$dumper = new Dumper($mapping, $parser);

/*echo $dumper->dumpArray([
    new NewsArticle(),
    'company',
]);*/

echo $dumper->dumpEntity(new NewsArticle());
//echo $dumper->dumpStatic('company');