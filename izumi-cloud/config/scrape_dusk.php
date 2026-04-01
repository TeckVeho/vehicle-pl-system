<?php

return [
    'chrome' => [
        '--headless',
        '--disable-gpu',
        '--window-size=1920,1200',
        '--no-zygote',

        /**
         * for Docker.
         */
        '--no-sandbox',
        '--disable-dev-shm-usage',
    ],
];
