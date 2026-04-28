<?php 
require_once 'model/conexion.php';
require_once 'funciones/ip.php'; // Devuelve la IP 
require_once 'funciones/mobile_detect.php'; // Devuelve Sistema operativo y el dispositivo 

session_start();
session_destroy();

header('Location: login.php');

