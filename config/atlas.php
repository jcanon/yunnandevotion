<?php

return [
    // Switch providers only after verifying attribution, licensing, coordinate alignment and regional reachability.
    'tile_url' => env('ATLAS_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'tile_attribution' => env('ATLAS_TILE_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
];
