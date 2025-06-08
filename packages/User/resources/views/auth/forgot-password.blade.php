<x-layouts.guest>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Forgot Password</h2>
            
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Send Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center text-muted mt-3">
        <a href="{{ route('login') }}" tabindex="-1">Back to login</a>
    </div>
</x-layouts.guest>