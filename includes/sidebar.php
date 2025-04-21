<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>" href="#dashboard" data-bs-toggle="tab">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'marketplace' ? 'active' : ''; ?>" href="#marketplace" data-bs-toggle="tab">
                    <i class="fas fa-store me-2"></i>Marketplace
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'my-room' ? 'active' : ''; ?>" href="#my-room" data-bs-toggle="tab">
                    <i class="fas fa-home me-2"></i>My Room
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'reservations' ? 'active' : ''; ?>" href="#reservations" data-bs-toggle="tab">
                    <i class="fas fa-calendar-check me-2"></i>My Reservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'maintenance' ? 'active' : ''; ?>" href="#maintenance" data-bs-toggle="tab">
                    <i class="fas fa-tools me-2"></i>Maintenance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'bills' ? 'active' : ''; ?>" href="#bills" data-bs-toggle="tab">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Bills & Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'renewal' ? 'active' : ''; ?>" href="#renewal" data-bs-toggle="tab">
                    <i class="fas fa-sync me-2"></i>Lease Renewal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'visitors' ? 'active' : ''; ?>" href="#visitors" data-bs-toggle="tab">
                    <i class="fas fa-users me-2"></i>Visitor Log
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" href="#profile" data-bs-toggle="tab">
                    <i class="fas fa-user-cog me-2"></i>Profile
                </a>
            </li>
        </ul>
    </div>
</div>
                    