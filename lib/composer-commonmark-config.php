<?php
require(dirname(__DIR__, 1) . '/vendor/autoload.php');

// Global Environment and Parsers
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

// Front Matter Extension
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;


// GFM Extension
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;


// Footnote Extension
use League\CommonMark\Extension\Footnote\FootnoteExtension;


// Mention Extension
use League\CommonMark\Extension\Mention\MentionExtension;


// Smart Punctuation Extension
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;


// Heading Permalink Extension
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;


// Table of Contents Extension
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;

// Description List Extension
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;


// Define your configuration, if needed
$config = [
    // Parser
    'renderer' => [
        'block_separator' => "\n",
        'inner_separator' => "\n",
        'soft_break'      => "\n",
    ],
    'commonmark' => [
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
        'unordered_list_markers' => ['-', '*', '+'],
    ],
    'html_input' => 'escape',
    'allow_unsafe_links' => false,
    'max_nesting_level' => PHP_INT_MAX,
    'slug_normalizer' => [
        'max_length' => 255,
    ],

    // Footnote Extension
    'footnote' => [
        'backref_class'      => 'footnote-backref',
        'backref_symbol'     => '↩',
        'container_add_hr'   => true,
        'container_class'    => 'footnotes',
        'ref_class'          => 'footnote-ref',
        'ref_id_prefix'      => 'fnref:',
        'footnote_class'     => 'footnote',
        'footnote_id_prefix' => 'fn:',
    ],

    // Mentions Extension
    'mentions' => [
        'instagram_handle' => [
            'prefix'    => '@',
            'pattern'   => '[A-Za-z0-9_]{1,15}(?!\w)',
            'generator' => 'https://instagram.com/%s',
        ],
    ],

    // Smart Punctuation Extension
    'smartpunct' => [
        'double_quote_opener' => '“',
        'double_quote_closer' => '”',
        'single_quote_opener' => '‘',
        'single_quote_closer' => '’',
    ],

    // Heading Permalink Extension
    'heading_permalink' => [
        'html_class' => 'heading-permalink',
        'id_prefix' => 'content',
        'apply_id_to_heading' => false,
        'heading_class' => '',
        'fragment_prefix' => 'content',
        'insert' => 'before',
        'min_heading_level' => 1,
        'max_heading_level' => 6,
        'title' => 'Permalink',
        'symbol' => '#',
        'aria_hidden' => true,
    ],

    // Table of Contents Extension
    'table_of_contents' => [
        'html_class' => 'table-of-contents',
        'position' => 'top',
        'style' => 'bullet',
        'min_heading_level' => 1,
        'max_heading_level' => 2,
        'normalize' => 'relative',
        'placeholder' => null,
    ],

    // Table Extension
    'table' => [
        'wrap' => [
            'enabled' => false,
            'tag' => 'div',
            'attributes' => [],
        ],
        'alignment_attributes' => [
            'left'   => ['align' => 'left'],
            'center' => ['align' => 'center'],
            'right'  => ['align' => 'right'],
        ],
    ],
];

// Configuring the Environment with all the CommonMark parsers/renderers
$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());

// Front Matter Extension
$environment->addExtension(new FrontMatterExtension());

// GFM Extension
$environment->addExtension(new GithubFlavoredMarkdownExtension());

// Footnote Extension
$environment->addExtension(new FootnoteExtension());

// Mention Extension
$environment->addExtension(new MentionExtension());

// Smart Punctuation Extension
$environment->addExtension(new SmartPunctExtension());


// Heading Permalink Extension
$environment->addExtension(new HeadingPermalinkExtension());


// Table of Contents Extension
$environment->addExtension(new TableOfContentsExtension());

// Description List Extension
$environment->addExtension(new DescriptionListExtension());

// Instantiate the converter engine and start converting some Markdown!
$converter = new MarkdownConverter($environment);
