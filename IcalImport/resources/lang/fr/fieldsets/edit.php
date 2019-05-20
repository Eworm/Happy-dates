<?php

return [

    'settings_section' => 'Paramètres',
    'settings_section_instruct' => 'Paramètres généraux pour ce flux.',

    'url' => 'Url',

    'enabled' => 'Activé',
    'enabled_instruct' => "Si désactivé, ce flux ne vérifiera pas de nouveaux articles.",

    'scheduling' => 'Programme',
    'scheduling_instruct' => "Temps d'attente entre les mises à jours, en minutes. Wait time between updates, in minutes.",

    'status' => 'Que faire avec les nouveaux articles',

    'cache_section' => 'Cache',

    'cache' => "Utilisation de la mise en cache",
    'cache_instruct' => "Si désactivé, ce flux n'utilisera pas la mise en cache. Cela pourrait rendre la récupération des nouveaux articles un peu plus lent. [Plus d'infos](http://simplepie.org/wiki/faq/how_does_simplepie_s_caching_http_conditional_get_system_work)",

    'cache_duration' => "Fraicheur de mémoire cache",
    'cache_duration_instruct' => "Combien de temps avant de demander une nouvelle copie (secondes).",

    'query_section' => 'Custom queries',

    'query' => 'Ajoutez des questions personnalisées Add custom queries',

    'query_grid' => 'Questions personnalisées Custom queries',

    'content_section' => 'Contenu',
    'content_section_instruct' => 'Où devrait aller le contenu du flux?',

    'publish_to' => 'Choisissez une collection pour les nouveaux articles',

    'item_title' => "Titre de l'article",
    'item_title_instruct' => 'Choisissez un champ existant ou ajouter un nom de domaine personnalisé.',

    'item_description' => "Description d'article",
    'item_description_instruct' => 'Choisissez un champ existant, ajoutez un nom de domaine personnalisé, ou désactivez la sauvegarde de la description.',

    'item_content' => "Contenu d'article",
    'item_content_instruct' => 'Choisissez un champ existant, ajoutez un nom de domaine personnalisé, ou désactivez la sauvegarde du contenu.',

    'item_permalink' => "Lien permanent d'article",
    'item_permalink_instruct' => 'Choisissez un champ existant, ajoutez un nom de domaine personnalisé, ou désactivez la sauvegarde du lien permanent.',

    'authors_section' => 'Auteurs',
    'authors_section_instruct' => "Options pour auteurs d'articles.",

    'author_options' => "Auteurs d'article",
    'author_options_instruct' => "Enregister l'auteur de l'article comme un nouvel utilisateur ou attribuer de nouveaux articles à un auteur existant.",

    'item_authors' => "Auteur d'article",
    'item_authors_instruct' => "Choisissez un champ existant, ajoutez un nom de domaine personnalisé, ou désactivez la sauvegarde de l'auteur.",

    'item_author' => 'Choisissez un utilisateur',
    'item_author_instruct' => 'Attribuer de nouveaux articles à.',

    'taxonomies_section' => 'Taxonomies & termes',
    'taxonomies_section_instruct' => "Options pour catégories d'article.",

    'item_taxonomies' => "Catégories d'article",
    'item_taxonomies_instruct' => "Quelle taxonomie dois-je utiliser pour les termes de l'article?",

    'custom_taxonomies' => 'Taxonomie de terme personnalisée',
    'custom_taxonomies_instruct' => 'Quelle taxonomie dois-je utiliser pour les termes personnalisés?',

    'custom_terms' => 'Termes personnalisés',
    'custom_terms_instruct' => 'Ces termes seront ajoutés à chaque nouvel article.',

    'images_section' => 'Images',
    'images_section_instruct' => "Options pour images d'article.",

    'item_thumbnail' => "Vignette d'article",
    'item_thumbnail_instruct' => "Choisissez un champ existant, ajoutez un nom de domaine personnalisé, ou désactivez l'enregistrement de la vignette",

    'save_images' => 'Enregistrez les vignettes',
    'save_images_instruct' => "Permet de télécharger des vignettes d'article.",

    'images_container' => "Choisissez un conteneur d'actif",
    'images_container_instruct' => "Les vignettes de l'article seront téléchargées et sauvegardées dans le conteneur d'actif choisi.",

];
