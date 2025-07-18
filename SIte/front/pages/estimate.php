<?php
// pages/estimate.php

// 1) On boote Laravel
require __DIR__ . '/../../api-ecodeli/vendor/autoload.php';
$app    = require __DIR__ . '/../../api-ecodeli/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 2) On capture la requête entrante (méthode POST, body multipart/form-data)
$request = Illuminate\Http\Request::capture();

// 3) On force l’URI interne vers /api/annonce/estimate
//    en dupliquant la requête et en réassignant REQUEST_URI :
$internal = $request->duplicate(
    $request->query->all(),
    $request->request->all(),
    $request->attributes->all(),
    $request->cookies->all(),
    $request->files->all(),
    array_merge($_SERVER, ['REQUEST_URI' => '/api/annonce/estimate'])
);

// 4) On exécute le kernel (qui passera par routes/api.php)
$response = $kernel->handle($internal);

// 5) On répercute les headers + status + contenu JSON
http_response_code($response->getStatusCode());
foreach ($response->headers->allPreserveCase() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getContent();

// 6) Terminer la requête
$kernel->terminate($request, $response);
