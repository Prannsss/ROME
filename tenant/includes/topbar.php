<!-- Content Wrapper -->
<div id="content-wrapper">
    <!-- Topbar -->
    <div class="topbar">
        <button id="sidebarToggleBtn" class="btn btn-link">
            <i class="fas fa-bars"></i>
        </button>

        <div class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger badge-counter"><?php echo $unpaid_bills_count + $pending_maintenance_count; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown">
                    <?php if ($unpaid_bills_count > 0): ?>
                    <a class="dropdown-item" href="dashboard.php?tab=bills">
                        <i class="fas fa-money-bill-wave mr-2 text-warning"></i>
                        You have <?php echo $unpaid_bills_count; ?> unpaid bills
                    </a>
                    <?php endif; ?>
                    <?php if ($pending_maintenance_count > 0): ?>
                    <a class="dropdown-item" href="dashboard.php?tab=maintenance">
                        <i class="fas fa-tools mr-2 text-info"></i>
                        You have <?php echo $pending_maintenance_count; ?> pending maintenance requests
                    </a>
                    <?php endif; ?>
                    <?php if ($unpaid_bills_count == 0 && $pending_maintenance_count == 0): ?>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-check-circle mr-2 text-success"></i>
                        No new notifications
                    </a>
                    <?php endif; ?>
                </div>
            </li>
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $tenant['fullname']; ?></span>
                    <i class="fas fa-user-circle fa-fw"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="dashboard.php?tab=profile">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">