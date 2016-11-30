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

$dumper = new Dumper($mapping, $parser);

echo $dumper->dumpArray([
    new NewsArticle(),
    'company',
]);

//echo $dumper->dumpEntity(new NewsArticle());
//echo $dumper->dumpStatic('company');