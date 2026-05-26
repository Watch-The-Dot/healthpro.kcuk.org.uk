<?php

use Brick\Schema\ThingConverter;
use Brick\StructuredData\Item;
use Symfony\Component\DomCrawler\Crawler;
use WatchTheDot\Plugins\RSSImporter\Fetch\Components\ArticleTitle;
use WatchTheDot\Plugins\RSSImporter\Support\OpenGraph;

describe( 'BBC News', function () {
    beforeEach(function () {
        $this->html = file_get_contents( __DIR__ . "/../bbc-news.html" );
        $this->url = "https://www.bbc.co.uk/news/articles/ckrg38vpzkxo";
    });

    it( 'fetches the title from the schema.org', function () {
        $schema = null;
        expect( ArticleTitle::from_schema_org( $schema ) )
            ->toBe("Man arrested on suspicion of murder over Bushey crossbow killings");
    } );

    it( 'fetches the title from the open graph', function () {
        $open_graph = OpenGraph::parse( $this->html );

        expect(ArticleTitle::from_open_graph($open_graph))  
            ->toBe("Man arrested on suspicion of murder over Bushey crossbow killings");
    } );

    it('fetches the title from the <title> tag', function () {
        $crawler = new Crawler($this->html);

        expect(ArticleTitle::from_title_tag($crawler))
            ->toBe("Man arrested on suspicion of murder over Bushey crossbow killings - BBC News");
    });
} );

describe( 'Scientific Reports', function () {
    beforeEach(function () {
        $this->html = file_get_contents( __DIR__ . "/../nature.html" );
        $this->url = "https://www.nature.com/articles/s41598-024-66525-9";
    });

    it( 'fetches the title from the schema.org', function () {
        $schema = new Item("Article");
        $schema->addProperty('title', "Sex-specific survival gene mutations are discovered as clinical predictors of clear cell renal cell carcinoma");
        
        $thing = (new ThingConverter())->convertItemToThing($schema);

        expect( ArticleTitle::from_schema_org( $thing ) )
            ->toBe("Sex-specific survival gene mutations are discovered as clinical predictors of clear cell renal cell carcinoma");
    } );

    it( 'fetches the title from the open graph', function () {
        $open_graph = OpenGraph::parse( $this->html );

        expect(ArticleTitle::from_open_graph($open_graph))  
            ->toBe("Sex-specific survival gene mutations are discovered as clinical predictors of clear cell renal cell carcinoma - Scientific Reports");
    } );

    it('fetches the title from the <title> tag', function () {
        $crawler = new Crawler($this->html);

        expect(ArticleTitle::from_title_tag($crawler))
            ->toBe("Sex-specific survival gene mutations are discovered as clinical predictors of clear cell renal cell carcinoma | Scientific Reports");
    });
} );

