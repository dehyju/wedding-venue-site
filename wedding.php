<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="company-logo.png" type="image/png">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="globalstyles.css">
  <title>Home</title>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="wedding.php">
        <img src="company-logo.png" width="50" height="50">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
      <div class="collapse navbar-collapse" id="navbarScroll">
        <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="wedding.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="venue.php">Venues</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div id="carouselExampleCaptions" class="carousel slide" data-bs-theme="dark" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="home-carousel-1.jpg" class="d-block w-100 h-auto" alt="...">
      </div>
      <div class="carousel-item">
        <img src="home-carousel-2.jpg" class="d-block w-100 h-auto" alt="...">
      </div>
      <div class="carousel-item">
        <img src="home-carousel-3.jpg" class="d-block w-100 h-auto" alt="...">
      </div>
    </div>
    <div class="carousel-caption">
      <h1>Romantic Rendezvous</h1>
      <p>Where Love Blossoms and Memories Flourish</p>
    </div>
    <style>
    .carousel-caption {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      padding: 20px; 
    }
    .carousel-caption h1{
      color: white;
      font-family: 'Crimson Text', serif;
      font-size: ;
      text-shadow: 
        -1px -1px 0 #000,  
        1px -1px 0 #000,
        -1px  1px 0 #000,
        1px  1px 0 #000;
    }

    .carousel-caption p{
      color: white;
      text-shadow: 
        -1px -1px 0 #000,  
        1px -1px 0 #000,
        -1px  1px 0 #000,
        1px  1px 0 #000;
    }
  </style>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
  </div>
      
</body>
</html>