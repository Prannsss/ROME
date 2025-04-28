<!-- Admin Sidebar -->
<div id="sidebar-wrapper">
    <a href="#" class="sidebar-brand">
        <i class="fas fa-home"></i> ROME
    </a>
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Core
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="../auth/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Management
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="../app/list.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'list.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-building"></i>
                <span>Room Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/register.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-plus-circle"></i>
                <span>Add New Room</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-users"></i>
                <span>Tenant Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/reservations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-calendar-check"></i>
                <span>Reservations</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Payments & Bills</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/maintenance-requests.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'maintenance-requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-tools"></i>
                <span>Maintenance Requests</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/visitor-logs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'visitor-logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Visitor Logs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../app/lease-renewals.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'lease-renewals.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>Lease Renewals</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Reports
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="../app/reports-and-analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports-and-analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-chart-area"></i>
                <span>Reports & Analytics</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Account
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="../app/settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>