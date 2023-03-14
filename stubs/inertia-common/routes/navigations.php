<?php

return [
    [
        "title" => "Dashboard",
        "icon" => "fas fa-tachometer-alt",
        "url" => url('/dashboard'),
    ],
    [
        "title" => "Example",
        "icon" => "fas fa-anchor",
        "url" => "#",
        "badge" => "Example",
        "childList" => [
            ["title" => "Example", "url" => url('/')],
        ],
    ],
];
