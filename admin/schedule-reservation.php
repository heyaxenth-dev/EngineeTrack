<?php 
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Schedule and Reservation</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active">Schedule and Reservation</li>
            </ol>
        </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="card">

        </div>
        <div id="calendar"></div>
    </section>
</main>
<!-- End #main -->


<script src="api/fullcalendar.js"></script>

<?php 
include 'includes/footer.php';
?>