<?php include('../includes/header.php'); ?>
<?php
include('../config.php'); // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "<script>alert('Failed to send message.');</script>";
    }
}
?>


<style>
    .contact-container {
        display: flex;
        flex-wrap: wrap;
        max-width: 1000px;
        margin: 50px auto;
        gap: 30px;
        padding: 20px;
    }

    .contact-info, .contact-form {
        flex: 1;
        min-width: 300px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .contact-info h3 {
        margin-bottom: 20px;
    }

    .contact-info p {
        margin: 10px 0;
        font-size: 16px;
        color: #333;
    }

    .contact-form h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .contact-form form div {
        margin-bottom: 15px;
    }

    .contact-form label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: bold;
    }

    .contact-form input, 
    .contact-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    .contact-form button {
        width: 100%;
        padding: 12px;
        background-color: green;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    .contact-form button:hover {
        scale: 1.05;
        background-color: #6a0fe0;
    }
</style>

<div class="contact-container">

    <!-- Left: Contact Info -->
   <!-- Left: Contact Info with Map -->
<div class="contact-info">
    <h3>📞 Get in Touch</h3>
    <p><strong>Email:</strong> support@triool.com</p>
    <p><strong>Phone:</strong> +91 98765 43210</p>
    <p><strong>Address:</strong> Bangalore, Karnataka, India</p>

    <!-- Embedded Google Map -->
    <div style="margin-top: 20px;">
        <iframe
            src="https://www.google.com/maps?q=Bangalore,Karnataka,India&output=embed"
            width="100%" height="250"
            style="border:0; border-radius: 10px;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>


    <!-- Right: Contact Form -->
    <div class="contact-form">
        <h2>Contact Us</h2>
        <form action="" method="post">
            <div>
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div>
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" required>
            </div>

            <div>
                <label for="message">Your Message</label>
                <textarea name="message" id="message" rows="5" required></textarea>
            </div>

            <button type="submit">Send Message</button>
        </form>
    </div>

</div>

<?php include('../includes/footer.php'); ?>
