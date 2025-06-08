<x-layouts.guest>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Reset Password</h2>
            
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center text-muted mt-3">
        <a href="{{ route('login') }}" tabindex="-1">Back to login</a>
    </div>
</x-layouts.guest>
