<?php
use Brick\Schema\Interfaces as Schema;
use Brick\Schema\SchemaReader;
use WatchTheDot\Plugins\RSSImporter\Fetch\SchemaCollection;

$SCHEMA = <<<HTML
<script type="application/ld+json">
{
  "mainEntity": {
    "headline": "Sex-specific survival gene mutations are discovered as clinical predictors of clear cell renal cell carcinoma",
    "description": "Although sex differences have been reported in patients with clear cell renal cell carcinoma (ccRCC), biological sex has not received clinical attention and genetic differences between sexes are poorly understood. This study aims to identify sex-specific gene mutations and explore their clinical significance in ccRCC. We used data from The Cancer Genome Atlas-Kidney Renal Clear Cell Carcinoma (TCGA-KIRC), The Renal Cell Cancer-European Union (RECA-EU) and Korean-KIRC. A total of 68 sex-related genes were selected from TCGA-KIRC through machine learning, and 23 sex-specific genes were identified through verification using the three databases. Survival differences according to sex were identified in nine genes (ACSS3, ALG13, ASXL3, BAP1, JADE3, KDM5C, KDM6A, NCOR1P1, and ZNF449). Female-specific survival differences were found in BAP1 in overall survival (OS) (TCGA-KIRC, p = 0.004; RECA-EU, p = 0.002; and Korean-KIRC, p = 0.003) and disease-free survival (DFS) (TCGA-KIRC, p = 0.001 and Korean-KIRC, p = 0.000004), and NCOR1P1 in DFS (TCGA-KIRC, p = 0.046 and RECA-EU, p = 0.00003). Male-specific survival differences were found in ASXL3 (OS, p = 0.017 in TCGA-KIRC; and OS, p = 0.005 in RECA-EU) and KDM5C (OS, p = 0.009 in RECA-EU; and DFS, p = 0.016 in Korean-KIRC). These results suggest that biological sex may be an important predictor and sex-specific tailored treatment may improve patient care in ccRCC.",
    "datePublished": "2024-07-09T00:00:00Z",
    "dateModified": "2024-07-09T00:00:00Z",
    "pageStart": "1",
    "pageEnd": "13",
    "license": "http://creativecommons.org/licenses/by/4.0/",
    "sameAs": "https://doi.org/10.1038/s41598-024-66525-9",
    "keywords": [
      "Cancer genomics",
      "Renal cell carcinoma",
      "Tumour biomarkers",
      "Clear cell renal cell carcinoma",
      "Sex",
      "Gene",
      "Mutation",
      "NGS",
      "Science",
      "Humanities and Social Sciences",
      "multidisciplinary"
    ],
    "image": [
      "https://media.springernature.com/lw1200/springer-static/image/art%3A10.1038%2Fs41598-024-66525-9/MediaObjects/41598_2024_66525_Fig1_HTML.png",
      "https://media.springernature.com/lw1200/springer-static/image/art%3A10.1038%2Fs41598-024-66525-9/MediaObjects/41598_2024_66525_Fig2_HTML.png",
      "https://media.springernature.com/lw1200/springer-static/image/art%3A10.1038%2Fs41598-024-66525-9/MediaObjects/41598_2024_66525_Fig3_HTML.png"
    ],
    "isPartOf": {
      "name": "Scientific Reports",
      "issn": ["2045-2322"],
      "volumeNumber": "14",
      "@type": ["Periodical", "PublicationVolume"]
    },
    "publisher": {
      "name": "Nature Publishing Group UK",
      "logo": {
        "url": "https://www.springernature.com/app-sn/public/images/logo-springernature.png",
        "@type": "ImageObject"
      },
      "@type": "Organization"
    },
    "author": [
      {
        "name": "Jia Hwang",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Hospital Pathology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Hye Eun Lee",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Hospital Pathology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Jin Seon Han",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Hospital Pathology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Moon Hyung Choi",
        "affiliation": [
          {
            "name": "Eunpyeong St. Mary’s Hospital, The Catholic University of Korea",
            "address": {
              "name": "Department of Radiology, College of Medicine, Eunpyeong St. Mary’s Hospital, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Sung Hoo Hong",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Urology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Sae Woong Kim",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Urology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Ji Hoon Yang",
        "affiliation": [
          {
            "name": "Sogang University",
            "address": {
              "name": "Department of Computer Science and Engineering, Sogang University, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Unsang Park",
        "affiliation": [
          {
            "name": "Sogang University",
            "address": {
              "name": "Department of Computer Science and Engineering, Sogang University, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Eun Sun Jung",
        "affiliation": [
          {
            "name": "Eunpyeong St. Mary’s Hospital, The Catholic University of Korea",
            "address": {
              "name": "Department of Hospital Pathology, College of Medicine, Eunpyeong St. Mary’s Hospital, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "@type": "Person"
      },
      {
        "name": "Yeong Jin Choi",
        "affiliation": [
          {
            "name": "The Catholic University of Korea",
            "address": {
              "name": "Department of Hospital Pathology, Seoul St. Mary’s Hospital, College of Medicine, The Catholic University of Korea, Seoul, Republic of Korea",
              "@type": "PostalAddress"
            },
            "@type": "Organization"
          }
        ],
        "email": "mdyjchoi@catholic.ac.kr",
        "@type": "Person"
      }
    ],
    "isAccessibleForFree": true,
    "@type": "ScholarlyArticle"
  },
  "@context": "https://schema.org",
  "@type": "WebPage"
}
</script>
HTML;

$SCHEMA = trim($SCHEMA);

beforeEach(function () use ( $SCHEMA ) {
    $this->schema_json = $SCHEMA;
});

it('searches correctly', function () {
    $reader = SchemaReader::forJsonLd();
    $collection = new SchemaCollection( $reader->readHtml( $this->schema_json, "https://www.nature.com/articles/s41598-024-66525-9" ) );

    expect( $collection->search( Schema\ScholarlyArticle::class ) )->toBeInstanceOf( Schema\ScholarlyArticle::class );
    expect( $collection->search( Schema\Article::class ) )->toBeInstanceOf( Schema\Article::class );
});