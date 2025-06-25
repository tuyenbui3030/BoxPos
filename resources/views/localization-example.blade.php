<!DOCTYPE html>
<html lang="@currentLang" dir="@langDirection">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.welcome') }} - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS (example) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    @livewireStyles
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">{{ config('app.name') }}</a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item">
                    <!-- Livewire Language Switcher -->
                    @livewire('language-switcher')
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <h1>@translation('app.welcome')</h1>
                <p>{{ __('app.loading') }}</p>
                
                <!-- Example usage of language-specific content -->
                @isLang('vi')
                    <div class="alert alert-info">
                        <strong>Chào mừng!</strong> Bạn đang sử dụng tiếng Việt.
                    </div>
                @endisLang
                
                @isLang('en')
                    <div class="alert alert-info">
                        <strong>Welcome!</strong> You are using English.
                    </div>
                @endisLang

                <!-- Example table with translations -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('app.dashboard') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.name') }}</th>
                                    <th>{{ __('app.email') }}</th>
                                    <th>{{ __('app.status') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ __('app.no_data') }}</td>
                                    <td colspan="3">{{ __('app.please_wait') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Static Language Switcher Alternative -->
                <div class="mt-4">
                    <h6>{{ __('app.language') }}:</h6>
                    @include('components.language-switcher')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
