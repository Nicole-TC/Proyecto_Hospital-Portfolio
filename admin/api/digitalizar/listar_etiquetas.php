<?php

header('Content-Type: application/json');

$datos = [
    [
        "id" => 1,
        "nombre_etiqueta" => "Urgente",
        "color_hex" => "#ff0000",
        "estado" => 1
    ],
    [
        "id" => 2,
        "nombre_etiqueta" => "Pendiente",
        "color_hex" => "#00ff00",
        "estado" => 0
    ]
];

echo json_encode($datos);