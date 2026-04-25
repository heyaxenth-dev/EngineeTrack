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

    <section class="section dashboard">
        <!-- Dashboard card details -->
        <div class="row">
            <!-- Pending Requests -->
            <div class="col-xxl-3 col-md-3">
                <div class="card info-card pending-requests-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            Pending Requests
                        </h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6>64</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Pending Requests -->

            <!-- Approved Today -->
            <div class="col-xxl-3 col-md-3">
                <div class="card info-card approved-today-card">
                    <div class="card-body">
                        <h5 class="card-title">Approved Today</h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6>42</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Approved Today -->

            <!-- Active Venues -->
            <div class="col-xxl-3 col-md-3">
                <div class="card info-card active-venues-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Venues</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bxs-building"></i>
                            </div>
                            <div class="ps-3">
                                <h6>24</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Active Venues -->

            <!-- Repairs On-going -->
            <div class="col-xxl-3 col-md-3">
                <div class="card info-card repairs-ongoing-card">
                    <div class="card-body">
                        <h5 class="card-title">Repairs On-going</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="ps-3">
                                <h6>8</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Repairs On-going -->
        </div>
        <!-- End Dashboard card details -->

        <!-- Filtler and Search -->
        <div class="card">
            <div class="card-body">
                <div class="card-title">Filter and Search</div>
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <select class="form-select" aria-label="Default select example">
                        <option selected>Open this select menu</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>

                    <select class="form-select" aria-label="Default select example">
                        <option selected>Open this select menu</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                    <select class="form-select" aria-label="Default select example">
                        <option selected>Open this select menu</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Calendar</h5>
                <div id='calendar'></div>
            </div>
        </div>
        <!-- End Calendar -->

        <div class="row">
            <!-- Recent Request -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Recent Request</h5>
                            <a href="#" class="link-opacity-75-hover">View All</a>
                        </div>
                        <div class="rounded border p-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title">Request 1</h4>
                                    <span class="badge rounded-pill text-bg-warning">Pending</span>
                                </div>
                                <p class="card-text">College of Engineering</p>

                                <span class="text-muted">
                                    <i class="bi bi-calendar"></i> Jan 24, 2026
                                </span>
                                <span class="text-muted px-3">
                                    <i class="bi bi-clock"></i> 10:00 AM - 11:00 AM
                                </span>

                                <div class="d-grid gap-2 d-block mt-4">
                                    <button class="btn btn-success" type="button">Approve</button>
                                    <button class="btn btn-danger" type="button">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End Recent Request -->

            <!-- Repair & Maintenance -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Repair & Maintenance</h5>
                            <a href="#" class="link-opacity-75-hover">View All</a>
                        </div>
                        <div class="rounded border p-2 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title">Request 1</h4>
                                    <span class="badge rounded-pill text-bg-info">On-going</span>
                                </div>
                                <p class="card-text">College of Engineering</p>
                                <span class="text-muted">
                                    <i class="bi bi-person"></i> Assigned Person: John Doe
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Repair & Maintenance -->
        </div>
    </section>
</main>
<!-- End #main -->


<script src="api/fullcalendar.js"></script>

<?php 
include 'includes/footer.php';
?>