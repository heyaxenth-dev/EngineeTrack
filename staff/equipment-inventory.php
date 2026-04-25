<?php
include 'includes/authentication.php'; 
include 'includes/header.php';
include 'includes/sidebar.php';
include 'alert.php';
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1><?= $renamed_pages[$current_page] ?></h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active"><?= $renamed_pages[$current_page] ?></li>
            </ol>
        </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Equipment List</h5>
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Total Quantity</th>
                                    <th>Total Quantity Borrowed</th>
                                    <th>Available Quantity</th>
                                    <th>Borrowing College/Office</th>
                                    <th>Expected Return Date</th>
                                    <th>Returned Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Unity Pugh</td>
                                    <td>9958</td>
                                    <td>Curicó</td>
                                    <td>2005/02/11</td>
                                    <td>37%</td>
                                    <td>37%</td>
                                    <td>37%</td>
                                    <td>
                                        <a href="#" class="btn btn-primary btn-sm">View and
                                            Update</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table with stripped rows -->

                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
<!-- End #main -->


<?php 
include 'includes/footer.php';
?>