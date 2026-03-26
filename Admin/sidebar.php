<?php
$role = $_SESSION['role'] ?? 'staff';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        <!-- LOGO -->
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span>Niels<strong> Brock University</strong></span>
        </div>

        <p class="sidebar-section-label">Navigation</p>

        <nav>
            <ul class="sidebar-nav">

                <!-- DASHBOARD -->
                <li>
                    <a href="<?= ($role === 'admin') ? 'dashboard.php' : 'staff_dashboard.php'; ?>"
                       class="sidebar-link <?= ($currentPage == 'dashboard.php' || $currentPage == 'staff_dashboard.php') ? 'active' : '' ?>">
                        Dashboard
                    </a>
                </li>

                <!-- ADMIN ONLY -->
                <?php if ($role === 'admin'): ?>

                <li>
                    <a href="students.php"
                       class="sidebar-link <?= $currentPage == 'students.php' ? 'active' : '' ?>">
                        Students
                    </a>
                </li>

                <li>
                    <a href="programmes.php"
                       class="sidebar-link <?= $currentPage == 'programmes.php' ? 'active' : '' ?>">
                        Programmes
                    </a>
                </li>

                <li>
                    <a href="staff.php"
                       class="sidebar-link <?= $currentPage == 'staff.php' ? 'active' : '' ?>">
                        👨‍🏫 Staff
                    </a>
                </li>

                <li>
                    <a href="create_admin.php"
                       class="sidebar-link <?= $currentPage == 'create_admin.php' ? 'active' : '' ?>">
                        ➕ Create User
                    </a>
                </li>

                <?php endif; ?>

                <!-- MODULES (BOTH CAN SEE) -->
                <li>
                    <a href="<?= ($role === 'admin') ? 'modules.php' : 'staff_dashboard.php'; ?>"
                       class="sidebar-link <?= $currentPage == 'modules.php' ? 'active' : '' ?>">
                        Modules
                    </a>
                </li>

                <!-- EXPORT (ADMIN ONLY) -->
                <?php if ($role === 'admin'): ?>
                <li>
                    <a href="export.php"
                       class="sidebar-link <?= $currentPage == 'export.php' ? 'active' : '' ?>">
                        Export Students
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>

        <!-- FOOTER -->
        <div class="sidebar-footer">
            <a href="../index.php" class="sidebar-link sidebar-link-muted">
                View Site
            </a>

            <a href="logout.php" class="sidebar-link sidebar-logout">
                Logout
            </a>
        </div>

    </div>
</aside>