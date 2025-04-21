<!-- Sidebar -->
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
            <a href="../tabs/dashboard-tab.php" class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
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
            <a href="../tabs/dashboard-tab.php?tab=my-room" class="nav-link <?php echo $active_tab == 'my-room' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-bed"></i>
                <span>My Room</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php?tab=bills" class="nav-link <?php echo $active_tab == 'bills' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Bills & Payments</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php?tab=maintenance" class="nav-link <?php echo $active_tab == 'maintenance' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-tools"></i>
                <span>Maintenance Requests</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php?tab=visitors" class="nav-link <?php echo $active_tab == 'visitors' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-user-friends"></i>
                <span>Visitor Logs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php?tab=marketplace" class="nav-link <?php echo $active_tab == 'marketplace' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-store"></i>
                <span>Marketplace</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Account
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="dashboard.php?tab=profile" class="nav-link <?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-user"></i>
                <span>Profile</span>
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