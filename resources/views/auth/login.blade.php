@extends('layouts.home')

@section('title', 'Login - RoomBooking')

@section('content')
<style>
    .login-section {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        max-width: 450px;
        width: 100%;
    }
    
    .login-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        padding: 30px;
        text-align: center;
        color: white;
    }
    
    .login-logo {
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }
    
    .login-body {
        padding: 40px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #1e3a8a;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-input {
        width: 100%;
        padding: 14px 40px 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s;
        background: #f8fafc;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .form-input.error {
        border-color: #ef4444;
    }
    
    .error-message {
        color: #ef4444;
        font-size: 13px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4b5563;
        font-size: 14px;
        cursor: pointer;
    }
    
    .remember-me input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #3b82f6;
        cursor: pointer;
    }
    
    .forgot-password {
        color: #3b82f6;
        font-size: 14px;
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .forgot-password:hover {
        color: #1e40af;
        text-decoration: underline;
    }
    
    .login-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }
    
    .login-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .register-link {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }
    
    .register-link p {
        color: #4b5563;
        font-size: 15px;
    }
    
    .register-link a {
        color: #1e40af;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .register-link a:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }
    
    .login-features {
        margin-top: 30px;
        padding: 20px;
        background: #f0f9ff;
        border-radius: 12px;
        border-left: 4px solid #3b82f6;
    }
    
    .features-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1e3a8a;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .feature-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4b5563;
        font-size: 13px;
    }
    
    .feature-icon {
        color: #3b82f6;
        font-size: 14px;
        width: 20px;
        text-align: center;
    }
    
    .info-box {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-box i {
        font-size: 16px;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    .toggle-password {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        font-size: 16px;
        z-index: 10;
        transition: color 0.3s;
    }
    
    .toggle-password:hover {
        color: #1e3a8a;
    }
</style>

<section class="login-section">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-building text-3xl text-blue-700"></i>
            </div>
            <h1 class="text-2xl font-bold">RoomBooking</h1>
            <p class="text-blue-200 mt-2">Sistem Peminjaman Ruangan Digital</p>
            <p class="mt-4 text-lg font-semibold">Login ke Akun Anda</p>
        </div>
        
        <!-- Form -->
        <div class="login-body">
            <!-- Info Box -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <span>Login menggunakan <strong>Username</strong> yang terdaftar</span>
            </div>
            
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <!-- Username ONLY (bukan email) -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           class="form-input @error('username') error @enderror"
                           placeholder="Masukkan username Anda"
                           required
                           autofocus>
                    @error('username')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                    <div id="login-feedback" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="login-error-text"></span>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input @error('password') error @enderror"
                               placeholder="Masukkan password Anda"
                               required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-password" id="forgotPassword">
                        <i class="fas fa-key mr-1"></i>Lupa password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login ke Akun
                </button>
            </form>
            
            <!-- Register Link -->
            <div class="register-link">
                <p>
                    Belum punya akun? 
                    <a href="{{ route('register') }}">Daftar Akun Baru</a>
                </p>
            </div>
            
            <!-- Security Info -->
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Data Anda aman dan terenkripsi
                </p>
            </div>
            
            <!-- Features -->
            <div class="login-features">
                <div class="features-title">
                    <i class="fas fa-star"></i>
                    <span>Manfaat RoomBooking</span>
                </div>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-bolt feature-icon"></i>
                        <span>Peminjaman Cepat</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar-check feature-icon"></i>
                        <span>Jadwal Real-time</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bell feature-icon"></i>
                        <span>Notifikasi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-bar feature-icon"></i>
                        <span>Analitik</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const togglePassword = document.getElementById('togglePassword');
        
        // Toggle password visibility
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
        
        // Username input validation (real-time)
        if (usernameInput) {
            usernameInput.addEventListener('blur', function() {
                const username = this.value.trim();
                const feedback = document.getElementById('login-feedback');
                const errorText = document.getElementById('login-error-text');
                
                if (username === '') {
                    this.classList.add('error');
                    feedback.style.display = 'flex';
                    errorText.textContent = 'Username tidak boleh kosong';
                } else if (username.length < 3) {
                    this.classList.add('error');
                    feedback.style.display = 'flex';
                    errorText.textContent = 'Username minimal 3 karakter';
                } else {
                    this.classList.remove('error');
                    feedback.style.display = 'none';
                }
            });
            
            usernameInput.addEventListener('focus', function() {
                this.classList.remove('error');
                const feedback = document.getElementById('login-feedback');
                feedback.style.display = 'none';
            });
        }
        
        // Password validation
        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            
            passwordInput.addEventListener('focus', function() {
                this.classList.remove('error');
            });
        }
        
        // Form submission validation
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (username === '') {
                usernameInput.classList.add('error');
                isValid = false;
                errorMessage = 'Username tidak boleh kosong';
            } else if (username.length < 3) {
                usernameInput.classList.add('error');
                isValid = false;
                errorMessage = 'Username minimal 3 karakter';
            }
            
            if (password === '') {
                passwordInput.classList.add('error');
                isValid = false;
                if (errorMessage) {
                    errorMessage += ' dan password tidak boleh kosong';
                } else {
                    errorMessage = 'Password tidak boleh kosong';
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Show error message
                const existingError = document.querySelector('.global-error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message global-error-message mt-3 text-center';
                errorDiv.style.marginTop = '15px';
                errorDiv.style.padding = '10px';
                errorDiv.style.backgroundColor = '#fee2e2';
                errorDiv.style.borderRadius = '8px';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errorMessage;
                loginForm.insertBefore(errorDiv, loginForm.querySelector('.remember-forgot'));
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    if (errorDiv.parentNode) {
                        errorDiv.remove();
                    }
                }, 5000);
            } else {
                // Disable button to prevent double submission
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            }
        });
        
        // Forgot password handler
        const forgotLink = document.getElementById('forgotPassword');
        if (forgotLink) {
            forgotLink.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Fitur lupa password akan segera hadir. Silakan hubungi administrator untuk reset password.');
            });
        }
    });
    
    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Clear form on page load if needed
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            const loginBtn = document.getElementById('loginBtn');
            if (loginBtn) {
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login ke Akun';
            }
        }
    });
</script>
@endsection