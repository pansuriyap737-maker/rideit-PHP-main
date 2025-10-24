
<link rel="stylesheet" href="../assets/css/style.css">

<style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    *{
        font-family: 'poppins';
    }
    .stat-box {
    background: #f1f1f1;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    width: 180px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-box h2 {
    font-size: 36px;
    color: #6a0fe0;;
    margin: 0;
}

.stat-box p {
    font-size: 16px;
    color: #333;
    margin-top: 10px;
}

.contact-link {
    display: inline-block;
    padding: 10px 20px; 
    background-color: green; 
    color: white; 
    text-decoration: none; 
    border-radius: 8px; 
    transition: transform 0.3s ease;
}

.contact-link:hover {
    background-color: #6a0fe0;
    transform: scale(1.05);
}



</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function countUp(id, end, duration) {
        let el = document.getElementById(id);
        let start = 0;
        let increment = Math.ceil(end / (duration / 30)); // ~30fps
        let counter = setInterval(function () {
            start += increment;
            if (start >= end) {
                el.innerText = end;
                clearInterval(counter);
            } else {
                el.innerText = start;
            }
        }, 30);
    }

    countUp("car-count", 52, 2000); // 2 seconds
    countUp("city-count", 20, 1500); // 1.5 seconds
    // A+ rating is static
});
</script>


<?php include('../includes/header.php'); ?>


<main style="padding: 20px;">
    <h1 style="text-align: center;">About Us</h1>
    <p style="max-width: 800px; margin: 0 auto; text-align: center;">
        Tripool is an innovative carsharing platform designed to make travel more efficient, affordable, and sustainable.
        Whether you're a car owner looking to earn extra income or a passenger needing a ride, Tripool connects people to create a smarter transport ecosystem.
    </p>

    <section style="max-width: 900px; margin: 40px auto; display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
        <div style="flex: 1; min-width: 250px; background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3>🚗 Our Mission</h3>
            <p>To simplify car sharing by providing a secure and user-friendly platform for both riders and car owners.</p>
        </div>

        <div style="flex: 1; min-width: 250px; background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3>🌍 Our Vision</h3>
            <p>Reduce environmental impact through shared mobility and build a community of responsible users.</p>
        </div>

        <div style="flex: 1; min-width: 250px; background: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3>🤝 Our Values</h3>
            <p>Trust, safety, community, and innovation guide everything we do at Tripool.</p>
        </div>
    </section>

    <section style="max-width: 800px; margin: 60px auto; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">

    <div class="stat-box">
        <h2 id="car-count">0</h2>
        <p>Total Cars</p>
    </div>

    <div class="stat-box">
        <h2 id="city-count">0</h2>
        <p>Cities Covered</p>
    </div>

    <div class="stat-box">
        <h2 id="rating">A+</h2>
        <p>Start Rating</p>
    </div>

</section>


    <div style="text-align: center; margin-top: 30px;">
        <a href="contact.php" class="contact-link">Contact Us</a>
    </div>
</main>

<?php include('../includes/footer.php'); ?>
