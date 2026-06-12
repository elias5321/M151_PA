<?php /* $activeNav muss vor dem Include gesetzt sein: 'dashboard' | 'borrow' | 'my_loans' | 'devices' | 'profile' */ ?>
<nav class="navbar navbar-expand-lg navbar-light" style="min-height: 60px">
    <a href="index.php" class="navbar-brand">
        <img src="https://web.fhnw.ch/fhnw-styleguide-v5/assets/img/fachhochschule-nordwestschweiz-fhnw-logo.svg" alt="FHNW - Fachhochschule Nordwestschweiz">
        <span class="navbar-title">IT Ausleihesystem</span>
    </a>
    <span class="navbar-title d-sm-none">IT Ausleihesystem</span>
    <ul class="navbar-nav ml-auto align-items-center flex-row">
        <li class="nav-item">
            <a class="nav-link<?= $activeNav === 'dashboard' ? ' active font-weight-bold' : '' ?>" href="index.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $activeNav === 'borrow' ? ' active font-weight-bold' : '' ?>" href="borrow.php">Ausleihen</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= $activeNav === 'my_loans' ? ' active font-weight-bold' : '' ?>" href="my_loans.php">Meine Ausleihen</a>
        </li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link<?= $activeNav === 'devices' ? ' active font-weight-bold' : '' ?>" href="devices.php">Geräte</a>
            </li>
        <?php endif; ?>
        <li class="nav-item dropdown ml-auto">
            <a class="nav-link dropdown-toggle<?= $activeNav === 'profile' ? ' active font-weight-bold' : '' ?>"
               href="#" id="userDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <?= safe($_SESSION['username']) ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="userDropdown">
                <a class="dropdown-item<?= $activeNav === 'profile' ? ' active' : '' ?>" href="profile.php">Mein Profil</a>
                <div class="dropdown-divider"></div>
                <form method="post" action="logout.php">
                    <button type="submit" class="dropdown-item text-danger">Abmelden</button>
                </form>
            </div>
        </li>
    </ul>
</nav>
