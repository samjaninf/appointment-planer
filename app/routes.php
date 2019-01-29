<?php

// Add ajax route callbacks
$app->get('/ajax/scheduler', 'AjaxController:scheduleEvent');

// Add route callbacks
$app->get('/', 'MainController:index');
$app->get('/vorgespraech', 'MainController:event');
$app->post('/vorgespraech', 'MainController:registerEvent');
