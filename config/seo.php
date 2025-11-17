<?php

return [
    'normalize_query' => [
        'drop_params' => ['utm_source','utm_medium','utm_campaign','gclid','fbclid'],
        'drop_page_one' => true,
        'default_sort_values' => ['sort' => 'default'],
    ],
];
