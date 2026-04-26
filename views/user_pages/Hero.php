<?php
// views/user_pages/Hero.php
// Expects from parent: $slides
?>
<div id="heroSlider"
     class="carousel slide carousel-fade"
     data-bs-ride="carousel"
     data-bs-interval="5000">

  <div class="carousel-indicators">
    <?php foreach ($slides as $i => $slide): ?>
      <button type="button"
              data-bs-target="#heroSlider"
              data-bs-slide-to="<?= $i ?>"
              <?= ($i === 0) ? 'class="active" aria-current="true"' : '' ?>
              aria-label="Slide <?= $i + 1 ?>">
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Slides -->
  <div class="carousel-inner h-100">
    <?php foreach ($slides as $i => $slide): ?>
      <div class="carousel-item h-100 <?= ($i === 0) ? 'active' : '' ?>">

        <!-- Background photo -->
        <div class="slide-bg"
             style="background-image: url('<?= htmlspecialchars($slide['image']) ?>');"></div>

        <!-- Colour overlay -->
        <div class="slide-overlay"
             style="background: <?= $slide['gradient'] ?>;"></div>

        <!-- Text content -->
        <div class="slide-content">
          <span class="slide-tag"><?= htmlspecialchars($slide['tag']) ?></span>
          <h1 class="slide-title"><?= htmlspecialchars($slide['title']) ?></h1>
          <p class="slide-sub"><?= htmlspecialchars($slide['sub']) ?></p>
          <a href="products.php" class="slide-btn">
            <?= htmlspecialchars($slide['btn']) ?> &nbsp;→
          </a>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- Prev button -->
  <button class="carousel-control-prev"
          type="button"
          data-bs-target="#heroSlider"
          data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>

  <!-- Next button -->
  <button class="carousel-control-next"
          type="button"
          data-bs-target="#heroSlider"
          data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>

</div>