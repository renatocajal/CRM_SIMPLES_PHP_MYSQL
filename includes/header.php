<?php
require_once 'auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Prospecção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.08); }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,.05); border: none; margin-bottom: 20px; }
        .tag-badge { font-size: 0.85em; padding: 5px 10px; border-radius: 4px; color: #fff; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><i class="fas fa-bullseye text-warning me-2"></i>CRM Vendas</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="contatos.php">Contatos</a></li>
        <li class="nav-item"><a class="nav-link" href="pipeline.php">Pipeline</a></li>
        <li class="nav-item"><a class="nav-link" href="produtos.php">Produtos</a></li>
        <li class="nav-item"><a class="nav-link" href="tags.php">Tags</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><span class="nav-link text-white-50"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_email']) ?></span></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid px-4">
