<?php 
$role = $_SESSION['role'] ?? ''; 
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-soft">
  <div class="container-fluid px-4">
    <a class="navbar-brand brand-soft" href="index.php">
      <span class="mark">E