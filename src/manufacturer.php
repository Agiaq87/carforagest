<?php

session_start();

$_SESSION['import_status'] = json_encode([
    "percentage" => 0,
    "message" => "Avvio importazione...",
    "completed" => false
]);

$totalSteps = 100; // Simula il numero di passi

for ($i = 1; $i <= $totalSteps; $i++) {
    sleep(3); // Simula il tempo di elaborazione
    $_SESSION['import_status'] = json_encode([
        "percentage" => ($i / $totalSteps) * 100,
        "message" => "Importazione in corso... Step $i di $totalSteps",
        "completed" => false
    ]);
}

$_SESSION['import_status'] = json_encode([
    "percentage" => 100,
    "message" => "Importazione completata!",
    "completed" => true
]);