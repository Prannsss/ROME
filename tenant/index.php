<?php
// Include necessary files
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/ROME/includes/helpers.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /ROME/auth/login.php');
    exit;
}

// Get active tab from URL parameter
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Include header
include('includes/header.php');
?>

<div id="wrapper">
    <!-- Sidebar -->
    <?php include('includes/sidebar.php'); ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <!-- Topbar -->
            <?php include('includes/topbar.php'); ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <?php
                // Load tab content based on active tab
                if ($active_tab == 'dashboard') {
                    include('tabs/dashboard-tab.php');
                } elseif ($active_tab == 'my-room') {
                    include('tabs/my-room-tab.php');
                } elseif ($active_tab == 'bills') {
                    include('tabs/bills-tab.php');
                } elseif ($active_tab == 'maintenance') {
                    include('tabs/maintenance-tab.php');
                } elseif ($active_tab == 'visitors') {
                    include('tabs/visitors-tab.php');
                } elseif ($active_tab == 'marketplace') {
                    include('tabs/marketplace-tab.php');
                } elseif ($active_tab == 'favorites') {
                    include('tabs/favorites-tab.php');
                } elseif ($active_tab == 'profile') {
                    include('tabs/profile-tab.php');
                } else {
                    include('tabs/dashboard-tab.php');
                }
                ?>
            </div>
            <!-- End Page Content -->
        </div>
        <!-- End Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; ROME 2023</span>
                </div>
            </div>
        </footer>
        <!-- End Footer -->
    </div>
    <!-- End Content Wrapper -->
</div>

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../assets/js/tenant-dashboard.js"></script>

</body>
</html>