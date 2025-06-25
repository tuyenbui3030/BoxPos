@extends('layouts.app')

@section('title', __('app.language') . ' Test')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    {{ __('app.language') }} {{ __('app.settings') }}
                </h2>
                <div class="text-muted mt-1">
                    {{ __('app.select_language') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('app.language') }} Demo</h3>
                    </div>
                    <div class="card-body">
                        <!-- Language Switcher -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('app.select_language') }}:</label>
                            <div class="d-flex gap-2">
                                <a href="{{ language_url('en') }}" class="btn {{ is_current_language('en') ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    🇺🇸 English
                                </a>
                                <a href="{{ language_url('vi') }}" class="btn {{ is_current_language('vi') ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    🇻🇳 Tiếng Việt
                                </a>
                            </div>
                        </div>

                        <!-- Sample Content -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ __('app.dashboard') }}</h4>
                                <ul class="list-group">
                                    <li class="list-group-item">{{ __('app.welcome') }}</li>
                                    <li class="list-group-item">{{ __('app.settings') }}</li>
                                    <li class="list-group-item">{{ __('app.profile') }}</li>
                                    <li class="list-group-item">{{ __('app.logout') }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4>{{ __('app.actions') }}</h4>
                                <div class="btn-list">
                                    <button class="btn btn-primary">{{ __('app.save') }}</button>
                                    <button class="btn btn-success">{{ __('app.create') }}</button>
                                    <button class="btn btn-warning">{{ __('app.edit') }}</button>
                                    <button class="btn btn-danger">{{ __('app.delete') }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Table -->
                        <div class="mt-4">
                            <h4>{{ __('app.no_data') }} Example</h4>
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
                                        <td colspan="4" class="text-center text-muted">
                                            {{ __('app.no_data') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Sample Messages -->
                        <div class="mt-4">
                            <h4>{{ __('app.notice') }}</h4>
                            <div class="alert alert-success">
                                <strong>{{ __('app.success') }}!</strong> {{ __('app.data_saved') }}
                            </div>
                            <div class="alert alert-info">
                                <strong>{{ __('app.info') }}:</strong> {{ __('app.please_wait') }}
                            </div>
                            <div class="alert alert-warning">
                                <strong>{{ __('app.warning') }}!</strong> {{ __('app.invalid_input') }}
                            </div>
                        </div>

                        <!-- Current Language Info -->
                        <div class="mt-4">
                            <h4>{{ __('app.language') }} Information</h4>
                            @php $currentLang = get_current_language(); @endphp
                            <div class="card">
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-3">Current Language:</dt>
                                        <dd class="col-sm-9">{{ $currentLang['flag'] }} {{ $currentLang['native'] }} ({{ $currentLang['code'] }})</dd>
                                        
                                        <dt class="col-sm-3">Direction:</dt>
                                        <dd class="col-sm-9">@langDirection</dd>
                                        
                                        <dt class="col-sm-3">Laravel Locale:</dt>
                                        <dd class="col-sm-9">@currentLang</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
