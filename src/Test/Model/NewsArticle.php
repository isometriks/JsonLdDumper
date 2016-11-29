<?php

namespace Isometriks\JsonLdDumper\Test\Model;

class NewsArticle
{
    public $headline = 'Here is a headline';
    public $image;
    public $author;
    public $published;

    public function __construct()
    {
        $this->author = new Author();
        $this->image = new Image();
        $this->published = new \DateTime();
    }
}
