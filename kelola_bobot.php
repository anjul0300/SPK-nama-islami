<?php
session_start();
require_once 'auth.php';
require_role('admin');
header('Location: input_preferensi_kriteria.php');
exit;
?>
