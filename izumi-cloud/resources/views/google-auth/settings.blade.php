<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth2 Settings - Workshift System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
        .token-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        .btn-google {
            background-color: #4285f4;
            border-color: #4285f4;
            color: white;
        }
        .btn-google:hover {
            background-color: #3367d6;
            border-color: #3367d6;
            color: white;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="display-5 fw-bold text-primary">
                        <i class="fab fa-google me-2"></i>
                        Google OAuth2 Settings
                    </h1>
                    <p class="text-muted">Manage Google authentication for Workshift Spreadsheet System</p>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Main Card -->
                <div class="card">
                    <div class="card-body p-4">
                        <!-- Connection Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    <i class="fas fa-link me-2"></i>
                                    Connection Status
                                </h5>
                                @if($isAuthenticated)
                                    <span class="badge bg-success status-badge">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Connected
                                    </span>
                                @else
                                    <span class="badge bg-danger status-badge">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Not Connected
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-outline-primary btn-sm" onclick="testConnection()">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Test Connection
                                </button>
                            </div>
                        </div>

                        <!-- User Info -->
                        @if($userInfo)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-user me-2"></i>
                                    User Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> {{ $userInfo['email'] ?? 'N/A' }}</p>
                                        <p><strong>Name:</strong> {{ $userInfo['name'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>ID:</strong> {{ $userInfo['id'] ?? 'N/A' }}</p>
                                        <p><strong>Picture:</strong> 
                                            @if(isset($userInfo['picture']))
                                                <img src="{{ $userInfo['picture'] }}" alt="Avatar" class="rounded-circle" width="30" height="30">
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Token Info -->
                        @if($tokenInfo)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-key me-2"></i>
                                    Token Information
                                </h6>
                                <div class="token-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Access Token:</strong> {{ $tokenInfo['access_token'] }}</p>
                                            <p><strong>Refresh Token:</strong> {{ $tokenInfo['refresh_token'] }}</p>
                                            <p><strong>Token Type:</strong> {{ $tokenInfo['token_type'] }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Expires In:</strong> {{ $tokenInfo['expires_in'] }}</p>
                                            <p><strong>Expires At:</strong> {{ $tokenInfo['expires_at'] ?? 'N/A' }}</p>
                                            <p><strong>Created:</strong> {{ $tokenInfo['created'] }}</p>
                                        </div>
                                    </div>
                                    @if(isset($tokenInfo['is_expired']) && $tokenInfo['is_expired'])
                                        <div class="alert alert-warning mt-2 mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Token has expired!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if($isAuthenticated)
                                        <form action="{{ route('google-auth.logout') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i>
                                                Logout
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('google-auth.authenticate') }}" class="btn btn-google">
                                            <i class="fab fa-google me-2"></i>
                                            Sign in with Google
                                        </a>
                                    @endif
                                    
                                    <a href="/" class="btn btn-outline-secondary">
                                        <i class="fas fa-home me-2"></i>
                                        Back to Home
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Environment Info -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Environment Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                                <p><strong>App Name:</strong> {{ config('app.name') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Root Folder:</strong> WS_Spreadsheet{{ app()->environment() !== 'production' ? '_' . app()->environment() : '' }}</p>
                                <p><strong>Spreadsheet Format:</strong> Department{{ app()->environment() !== 'production' ? '_' . app()->environment() : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testConnection() {
            fetch('{{ route("google-auth.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error.message);
            });
        }
    </script>
</body>
</html> 