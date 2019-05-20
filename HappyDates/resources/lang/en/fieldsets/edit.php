<?php

return [

    'settings_section' => 'Settings',
    'settings_section_instruct' => 'General settings for this feed.',

    'url' => 'Url',

    'enabled' => 'Enabled',
    'enabled_instruct' => "If disabled, this feed won't check for new articles.",

    'scheduling' => 'Schedule',
    'scheduling_instruct' => 'Wait time between updates (minutes).',

    'status' => 'What to do with new articles',

    'cache_section' => 'Cache',

    'cache' => "Use caching",
    'cache_instruct' => "If disabled, this feed won't use caching. This might make fetching new articles a little slower. [More info](http://simplepie.org/wiki/faq/how_does_simplepie_s_caching_http_conditional_get_system_work)",

    'cache_duration' => "Cache freshness",
    'cache_duration_instruct' => "How long before asking for a fresh copy (seconds).",

    'query_section' => 'Custom queries',

    'query' => 'Add custom queries',

    'query_grid' => 'Custom queries',

    'content_section' => 'Content',
    'content_section_instruct' => 'Where should the feed content go?',

    'publish_to' => 'Choose a collection for new articles',

    'item_title' => 'Article title',
    'item_title_instruct' => 'Choose an existing field or add a custom fieldtype name.',

    'item_description' => 'Article description',
    'item_description_instruct' => 'Choose an existing field, add a custom fieldtype name, or disable saving the description.',

    'item_content' => 'Article content',
    'item_content_instruct' => 'Choose an existing field, add a custom fieldtype name, or disable saving the content.',

    'item_permalink' => 'Article permalink',
    'item_permalink_instruct' => 'Choose an existing field, add a custom fieldtype name, or disable saving the permalink.',

    'authors_section' => 'Authors',
    'authors_section_instruct' => 'Options for article authors.',

    'author_options' => 'Article authors',
    'author_options_instruct' => 'Save the article author as a new user or attribute new articles to an existing one.',

    'item_authors' => 'Article author',
    'item_authors_instruct' => 'Choose an existing field, add a custom fieldtype name, or disable saving the author.',

    'item_author' => 'Choose a user',
    'item_author_instruct' => 'To attribute new articles to.',

    'taxonomies_section' => 'Taxonomies & terms',
    'taxonomies_section_instruct' => 'Options for article categories.',

    'item_taxonomies' => 'Article categories',
    'item_taxonomies_instruct' => 'Which taxonomy should I use for article terms?',

    'custom_taxonomies' => 'Custom term taxonomy',
    'custom_taxonomies_instruct' => 'Which taxonomy should I use for custom terms?',

    'custom_terms' => 'Custom terms',
    'custom_terms_instruct' => 'These terms will be added to each new article.',

    'images_section' => 'Images',
    'images_section_instruct' => 'Options for article images.',

    'item_thumbnail' => 'Article thumbnail',
    'item_thumbnail_instruct' => 'Choose an existing field, add a custom fieldtype name, or disable saving the thumbnail.',

    'save_images' => 'Save thumbnails',
    'save_images_instruct' => 'Enable to download article thumbnails.',

    'images_container' => 'Choose an asset container',
    'images_container_instruct' => 'Article thumbnails will be downloaded and saved in the chosen asset container.',

];
