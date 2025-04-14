<!-- Header -->
<header class="header">

    <div class="page-title-box">
        <h3>{{ env('APP_NAME') }}</h3>
    </div>
    <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
    <ul class="nav user-menu d-none d-lg-flex align-items-center">

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" id="navbarDropdownUserImage" href="#"
                role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="profile-logo position-relative">
                    <img src="{{ !empty(auth()->user()->avatar) ? asset('storage/users/' . auth()->user()->avatar) : asset('assets/img/user.jpg') }}"
                        alt="user" class="rounded-circle" width="40" height="40">
                </span>
                <span class="mx-2 text-light">{{ auth()->user()->firstname ?? '' }}</span>
                <i class="fa fa-chevron-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="navbarDropdownUserImage">
                <div class="dropdown-header d-flex align-items-center">
                    <span class="profile-logo me-2">
                        <img src="{{ !empty(auth()->user()->avatar) ? asset('storage/users/' . auth()->user()->avatar) : asset('assets/img/user.jpg') }}"
                            alt="user" class="rounded-circle" width="40" height="40">
                    </span>
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name fw-bold">{{ auth()->user()->firstname ?? '' }}
                            {{ auth()->user()->lastname ?? '' }}
                        </div>
                        <div class="dropdown-user-details-email text-muted">{{ auth()->user()->email ?? '' }}</div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('profile') }}">
                    <i class="fa fa-cog me-2"></i> Account
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="dropdown-item" type="submit">
                        <i class="fa fa-sign-out me-2"></i> Logout
                    </button>

                  

                </form>

            </div>
        </li>
    </ul>

    <div class="dropdown d-lg-none mobile-user-menu">
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('profile') }}">My Profile</a>
            <a class="dropdown-item" href="#">Settings</a>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="dropdown-item">Logout</button>
            </form>
        </div>
    </div>
</header>
<!-- /Header -->


