<?php include('../includes/header.php'); ?>

<style>
    /* Existing styles from your PHP file */
    /* === Slider Section === */
    .slider-container {
        width: 100%;
        height: 100vh;
    }

    /* Now adding the styles from Home.css */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    *{
        font-family: poppins;
    }
    .home-container {
      text-align: center;
    }

    #welcome-para {
      margin-top: 10px;
      font-family: "Poppins", sans-serif;
      font-size: 35px;
      margin-bottom: 0.5rem;
    }

    .hero-section p {
      font-family: "Poppins", sans-serif;
      font-size: 16px;
      color: #555;
      margin-bottom: 2rem;
    }

    .features {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-direction: wrap;
      margin-bottom: 2rem;
    }

    .feature-card {
      background-color: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 6px rgba(0,0,0,0.1);
      width: 315px;
    }

    .feature-card h4 {
      font-family: "Poppins", sans-serif;
      font-size: 24px;
      margin-bottom: 5px;
      text-align: left;
    }

    .feature-card p {
      font-family: "Poppins", sans-serif;
      font-size: 18px;
      margin-bottom: 5px;
      text-align: left;
    }

    .join-button {
      font-family: "Poppins", sans-serif;
      font-size: 15px;
      background-color: green;
      color: white;
      border: none;
      padding: 15px 25px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }

    .join-button:hover {
      transform: scale(110%);
      background-color: #8000ff;
    }

    .regi {
      text-decoration: none;
      color: white;
    }

    .cover-img {
      width: 100%;
      height: 100%;
    }
</style>

<!-- Static Photo Section -->
<div class="slider-container">
    <img src="../assets/images/Lambo.jpg" alt="Static Image" style="width: 100%; height: 100vh; object-fit: cover;">  <!-- Single static image -->
</div>

<main style="padding: 30px;">
    <h1 style="text-align: center;" id="welcome-para">Welcome to RideIt - Carsharing System</h1>
    <p style="text-align: center;">We help connect car owners with people who need rides. Safe, smart, and sustainable travel.</p>

    <!-- Feature Cards -->
    <section style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-top: 40px;" class="features">
        <div class="card feature-card">
            <h3>✔ Easy Car Booking</h3>
            <p>Book cars from nearby users with just a few clicks.</p>
        </div>
        <div class="card feature-card">
            <h3>✔ Trusted Owners</h3>
            <p>All owners and cars are verified for safe journeys.</p>
        </div>
        <div class="card feature-card">
            <h3>✔ Affordable Rides</h3>
            <p>Lower your travel costs with shared rides and rentals.</p>
        </div>
    </section>

    <div style="text-align: center; margin-top: 40px;">
        <a href="register.php" class="btn join-button">Join Now</a>
    </div>
</main>

<div class="custom-footer">
    <?php include('../includes/footer.php'); ?>
</div>
