<div>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Login to your account</h2>
            
            <!-- Success Message -->
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l5 5l10 -10"/>
                            </svg>
                        </div>
                        <div>
                            {{ session('message') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Lockout Warning -->
            @if (session()->has('login_attempts') && session('login_attempts') >= 3)
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v2m0 4v.01"/>
                                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
                            </svg>
                        </div>
                        <div>
                            Multiple failed login attempts detected. Account will be temporarily locked after 5 failed attempts.
                        </div>
                    </div>
                </div>
            @endif
            
            <form wire:submit="login" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        placeholder="your@email.com" 
                        wire:model="email" 
                        autocomplete="off">
                    @error('email') 
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-2">
                    <label class="form-label">
                        Password
                        <span class="form-label-description">
                            <a href="{{ route('password.request') }}">I forgot password</a>
                        </span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Your password" 
                        wire:model="password" 
                        autocomplete="off">
                    @error('password') 
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-2">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" wire:model="remember" id="remember">
                        <span class="form-check-label">Remember me on this device</span>
                    </label>
                    <small class="form-hint">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-info-circle" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="12" cy="12" r="9"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                            <polyline points="11,12 12,12 12,16 13,16"/>
                        </svg>
                        Với "Remember me", bạn sẽ không bao giờ bị logout khi còn hoạt động!
                    </small>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        <div wire:loading class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Sign in
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        Don't have an account yet? <a href="{{ route('register') }}" tabindex="-1">Sign up</a>
    </div>
</div>
