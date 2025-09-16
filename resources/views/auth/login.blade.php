@extends('layouts.auth')

@section('content')
    <div class="container-xxl d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="row justify-content-center w-100">
            <div class="col-md-4 col-lg-4 col-sm-8">

                <!-- Login Card -->
                <div class="card shadow-sm">
                    <div class="card-body">

                        <!-- Logo -->
                        <center>
                        <div class="app-brand demo">
                            <a href="{{ url('/') }}" class="app-brand-link">
                                <span class="app-brand-logo">
                                    <img src="{{ asset('logokti.jpg') }}" alt="KTI Logo"
                                        style="height: 95px; width: auto;">
                                </span>

                                <h2 class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase text-dark">
                                    Kutai Timber
                                    <medium class="d-block text-uppercase text-dark"
                                        style="font-size: 0.75rem;">Indonesia</medium>
                                </h2>
                            </a>
                        </div>
                        </center>
                        <!-- /Logo -->

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autofocus>
                                @error('email')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group input-group-merge">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required>
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3 d-flex justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember"> Remember Me </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"><small>Forgot Password?</small></a>
                                @endif
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary d-grid w-100">Log in</button>
                            </div>
                        </form>

                        {{-- <p class="text-center mb-0">
                            <span>New here?</span>
                            <a href="{{ route('register') }}"><span>Create an account</span></a>
                        </p> --}}

                    </div>
                </div>
                <!-- /Login Card -->

            </div>
        </div>
    </div>
@endsection
