<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafeteria — Products</title>
  <link rel="stylesheet" href="../../assets/css/admin.css">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
</head>
<body>
<?php
$activePage  = 'products';
$menuItems   = [
  ['id'=>'home',       'icon'=>'bi-house',         'label'=>'Home'],
  ['id'=>'orders',     'icon'=>'bi-bag-check',      'label'=>'Orders'],
  ['id'=>'dashboard',  'icon'=>'bi-grid',           'label'=>'Dashboard'],
  ['id'=>'products',   'icon'=>'bi-box-seam',       'label'=>'Products'],
  ['id'=>'users',      'icon'=>'bi-people',         'label'=>'Users'],
  ['id'=>'categories', 'icon'=>'bi-tag',            'label'=>'Categories'],

];
?>
<div class="candy-sidebar">
  <div class="sidebar-header">
    <p class="s-panel-title">Admin Panel</p>
    <p class="s-panel-sub">Cafeteria Management</p>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($menuItems as $m): ?>
      <a href="<?= $m['id'] ?>.php"
         class="s-link <?= $activePage === $m['id'] ? 'active' : '' ?>">
        <span class="s-icon"><i class="bi <?= $m['icon'] ?>"></i></span>
        <?= $m['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-3">
      <img src="https://ui-avatars.com/api/?name=Admin&background=e91e8c&color=fff&size=40"
           alt="Admin" class="rounded-circle" width="42" height="42">
      <div>
        <p class="s-admin-name">Admin Avatar</p>
        <a href="logout.php" class="s-admin-logout">LOGOUT</a>
      </div>
    </div>
  </div>
</div>  
</body>
</html>