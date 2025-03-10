@extends('layouts.auth')

@section('content')
<div class="preloader">Loading...</div>

<div class="login-container">
    <div class="login-box">
        <h1>Login</h1>
        <img class="company-logo" src="{{asset('assets/img/logo.png')}}" alt="Boxleo Logo">
        <form action="{{ route('login') }}" method="post">
            @csrf
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>

                @error('email')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>

                @error('password')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="actions">
                <label><input type="checkbox" name="remember"> Remember me</label>
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            </div>
            <button type="submit" class="login-btn">Login</button>

        </form>
    </div>
</div>

<script>
    // Wait for the image to load before showing the login container
    document.addEventListener('DOMContentLoaded', function() {
        const bgImage = new Image();
        bgImage.src = "/assets/img/bgs/login-bg.png"; // Update path if necessary

        bgImage.onload = function() {
            document.body.classList.remove('loading');
            document.body.classList.add('loaded');
        };
    });



    // document.addEventListener('DOMContentLoaded', function() {
    //     document.body.classList.remove('loading');
    //     document.body.classList.add('loaded');
    // });


    // Initial loading state
    document.body.classList.add('loading');
</script>
@endsection