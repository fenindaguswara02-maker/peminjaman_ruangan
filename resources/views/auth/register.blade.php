@extends('layouts.home')

@section('title', 'Register - RoomBooking')

@section('content')
<style>
    .register-section {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .register-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        max-width: 500px;
        width: 100%;
    }
    
    .register-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        padding: 30px;
        text-align: center;
        color: white;
    }
    
    .register-logo {
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
    
    .register-body {
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
        padding: 14px 16px;
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
    
    .form-input.success {
        border-color: #10b981;
    }
    
    .error-message {
        color: #ef4444;
        font-size: 13px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .success-message {
        color: #10b981;
        font-size: 13px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .register-btn {
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
    
    .register-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }
    
    .register-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .login-link {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }
    
    .login-link a {
        color: #1e40af;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .login-link a:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }
    
    .security-info {
        margin-top: 25px;
        padding: 20px;
        background: #f0f9ff;
        border-radius: 12px;
        border-left: 4px solid #3b82f6;
    }
    
    .security-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1e3a8a;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .security-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .security-list li {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4b5563;
        font-size: 14px;
        margin-bottom: 8px;
    }
    
    .security-list li:last-child {
        margin-bottom: 0;
    }
    
    .check-icon {
        color: #10b981;
        font-size: 14px;
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
    
    /* Live validation styles */
    .live-validation-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    .input-wrapper .form-input {
        padding-right: 40px;
    }
</style>

<section class="register-section">
    <div class="register-card">
        <!-- Header -->
        <div class="register-header">
            <div class="register-logo">
                <i class="fas fa-building text-3xl text-blue-700"></i>
            </div>
            <h1 class="text-2xl font-bold">RoomBooking</h1>
            <p class="text-blue-200 mt-2">Sistem Peminjaman Ruangan Digital</p>
            <p class="mt-4 text-lg font-semibold">Daftar Akun Baru</p>
        </div>
        
        <!-- Form -->
        <div class="register-body">
            <!-- Info Box -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <span><strong>Username</strong>, <strong>Email</strong>, dan <strong>Nomor Telepon</strong> harus unik dan belum terdaftar</span>
            </div>
            
            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf
                
                <!-- Username (UNIQUE) -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-at mr-2"></i>Username <span class="text-red-500">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="{{ old('username') }}" 
                               class="form-input @error('username') error @enderror"
                               placeholder="Masukkan username unik Anda"
                               required>
                    </div>
                    <div id="username-error" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="username-error-text"></span>
                    </div>
                    <div id="username-success" class="success-message" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <span>Username tersedia</span>
                    </div>
                    @error('username')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Nama Lengkap (BOLEH SAMA) -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-user mr-2"></i>Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           class="form-input @error('name') error @enderror"
                           placeholder="Masukkan nama lengkap Anda"
                           required>
                    @error('name')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Email (UNIQUE) -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope mr-2"></i>Email <span class="text-red-500">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="form-input @error('email') error @enderror"
                               placeholder="contoh@email.com"
                               required>
                    </div>
                    <div id="email-error" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="email-error-text"></span>
                    </div>
                    <div id="email-success" class="success-message" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <span>Email tersedia</span>
                    </div>
                    @error('email')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Nomor Telepon (UNIQUE) -->
                <div class="form-group">
                    <label for="no_telepon" class="form-label">
                        <i class="fas fa-phone mr-2"></i>Nomor Telepon
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               id="no_telepon" 
                               name="no_telepon" 
                               value="{{ old('no_telepon') }}" 
                               class="form-input @error('no_telepon') error @enderror"
                               placeholder="0812 3456 7890">
                    </div>
                    <div id="phone-error" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="phone-error-text"></span>
                    </div>
                    <div id="phone-success" class="success-message" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <span>Nomor telepon tersedia</span>
                    </div>
                    @error('no_telepon')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock mr-2"></i>Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input @error('password') error @enderror"
                           placeholder="Buat password yang kuat (minimal 6 karakter)"
                           required>
                    <div id="password-strength" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Password minimal 6 karakter</span>
                    </div>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                
                <!-- Konfirmasi Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-lock mr-2"></i>Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="form-input"
                           placeholder="Ulangi password Anda"
                           required>
                    <div id="password-match" class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Password tidak cocok</span>
                    </div>
                    <div id="password-match-success" class="success-message" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <span>Password cocok</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="register-btn" id="registerBtn">
                    <i class="fas fa-user-plus"></i>
                    Daftar Akun Baru
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="login-link">
                <p class="text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}">Login di sini</a>
                </p>
            </div>
            
            <!-- Security Info -->
            <div class="security-info">
                <div class="security-title">
                    <i class="fas fa-shield-alt"></i>
                    <span>Keamanan Akun</span>
                </div>
                <ul class="security-list">
                    <li>
                        <i class="fas fa-check check-icon"></i>
                        Username harus unik (tidak boleh sama dengan pengguna lain)
                    </li>
                    <li>
                        <i class="fas fa-check check-icon"></i>
                        Email harus unik (tidak boleh sama dengan pengguna lain)
                    </li>
                    <li>
                        <i class="fas fa-check check-icon"></i>
                        Nomor telepon harus unik (tidak boleh sama dengan pengguna lain)
                    </li>
                    <li>
                        <i class="fas fa-check check-icon"></i>
                        Password minimal 6 karakter
                    </li>
                    <li>
                        <i class="fas fa-check check-icon"></i>
                        Gunakan kombinasi huruf dan angka untuk password yang kuat
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('no_telepon');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    
    // CSRF Token untuk AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    
    // Fungsi untuk cek username via AJAX
    function checkUsername(username) {
        if (username.length < 3) {
            document.getElementById('username-error').style.display = 'flex';
            document.getElementById('username-error-text').textContent = 'Username minimal 3 karakter';
            document.getElementById('username-success').style.display = 'none';
            usernameInput.classList.add('error');
            usernameInput.classList.remove('success');
            return false;
        }
        
        return fetch('/check-username', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ username: username })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('username-error').style.display = 'flex';
                document.getElementById('username-error-text').textContent = 'Username sudah digunakan';
                document.getElementById('username-success').style.display = 'none';
                usernameInput.classList.add('error');
                usernameInput.classList.remove('success');
                return false;
            } else {
                document.getElementById('username-error').style.display = 'none';
                document.getElementById('username-success').style.display = 'flex';
                usernameInput.classList.remove('error');
                usernameInput.classList.add('success');
                return true;
            }
        })
        .catch(() => {
            return true;
        });
    }
    
    // Fungsi untuk cek email via AJAX
    function checkEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('email-error').style.display = 'flex';
            document.getElementById('email-error-text').textContent = 'Format email tidak valid';
            document.getElementById('email-success').style.display = 'none';
            emailInput.classList.add('error');
            emailInput.classList.remove('success');
            return false;
        }
        
        return fetch('/check-email', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('email-error').style.display = 'flex';
                document.getElementById('email-error-text').textContent = 'Email sudah terdaftar';
                document.getElementById('email-success').style.display = 'none';
                emailInput.classList.add('error');
                emailInput.classList.remove('success');
                return false;
            } else {
                document.getElementById('email-error').style.display = 'none';
                document.getElementById('email-success').style.display = 'flex';
                emailInput.classList.remove('error');
                emailInput.classList.add('success');
                return true;
            }
        })
        .catch(() => {
            return true;
        });
    }
    
    // Fungsi untuk cek nomor telepon via AJAX
    function checkPhone(phone) {
        if (phone.length > 0 && phone.length < 10) {
            document.getElementById('phone-error').style.display = 'flex';
            document.getElementById('phone-error-text').textContent = 'Nomor telepon minimal 10 digit';
            document.getElementById('phone-success').style.display = 'none';
            phoneInput.classList.add('error');
            phoneInput.classList.remove('success');
            return false;
        }
        
        if (phone.length === 0) {
            document.getElementById('phone-error').style.display = 'none';
            document.getElementById('phone-success').style.display = 'none';
            phoneInput.classList.remove('error', 'success');
            return true;
        }
        
        return fetch('/check-phone', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ no_telepon: phone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('phone-error').style.display = 'flex';
                document.getElementById('phone-error-text').textContent = 'Nomor telepon sudah terdaftar';
                document.getElementById('phone-success').style.display = 'none';
                phoneInput.classList.add('error');
                phoneInput.classList.remove('success');
                return false;
            } else {
                document.getElementById('phone-error').style.display = 'none';
                document.getElementById('phone-success').style.display = 'flex';
                phoneInput.classList.remove('error');
                phoneInput.classList.add('success');
                return true;
            }
        })
        .catch(() => {
            return true;
        });
    }
    
    // Fungsi validasi password
    function validatePassword() {
        const password = passwordInput.value;
        const isValid = password.length >= 6;
        
        if (!isValid && password.length > 0) {
            document.getElementById('password-strength').style.display = 'flex';
            passwordInput.classList.add('error');
        } else {
            document.getElementById('password-strength').style.display = 'none';
            passwordInput.classList.remove('error');
        }
        
        return isValid;
    }
    
    // Fungsi validasi konfirmasi password
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirm = passwordConfirmInput.value;
        const isValid = password === confirm && password.length > 0;
        
        if (confirm.length > 0) {
            if (isValid) {
                document.getElementById('password-match').style.display = 'none';
                document.getElementById('password-match-success').style.display = 'flex';
                passwordConfirmInput.classList.remove('error');
                passwordConfirmInput.classList.add('success');
            } else {
                document.getElementById('password-match').style.display = 'flex';
                document.getElementById('password-match-success').style.display = 'none';
                passwordConfirmInput.classList.add('error');
                passwordConfirmInput.classList.remove('success');
            }
        } else {
            document.getElementById('password-match').style.display = 'none';
            document.getElementById('password-match-success').style.display = 'none';
            passwordConfirmInput.classList.remove('error', 'success');
        }
        
        return isValid;
    }
    
    // Event listeners untuk live validation
    let usernameTimeout;
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        usernameTimeout = setTimeout(() => {
            checkUsername(this.value);
        }, 500);
    });
    
    let emailTimeout;
    emailInput.addEventListener('input', function() {
        clearTimeout(emailTimeout);
        emailTimeout = setTimeout(() => {
            checkEmail(this.value);
        }, 500);
    });
    
    let phoneTimeout;
    phoneInput.addEventListener('input', function() {
        clearTimeout(phoneTimeout);
        phoneTimeout = setTimeout(() => {
            checkPhone(this.value);
        }, 500);
    });
    
    passwordInput.addEventListener('input', function() {
        validatePassword();
        validatePasswordMatch();
    });
    
    passwordConfirmInput.addEventListener('input', function() {
        validatePasswordMatch();
    });
    
    // Form submission validation
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validasi semua field
        const isUsernameValid = await checkUsername(usernameInput.value);
        const isEmailValid = await checkEmail(emailInput.value);
        const isPhoneValid = await checkPhone(phoneInput.value);
        const isPasswordValid = validatePassword();
        const isPasswordMatchValid = validatePasswordMatch();
        const isNameValid = document.getElementById('name').value.trim() !== '';
        
        if (!isNameValid) {
            document.getElementById('name').classList.add('error');
            setTimeout(() => {
                document.getElementById('name').classList.remove('error');
            }, 3000);
            return;
        }
        
        if (!isUsernameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isPasswordMatchValid) {
            // Tampilkan error global
            const existingError = document.querySelector('.global-error');
            if (existingError) existingError.remove();
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message global-error mt-3 text-center';
            errorDiv.style.marginTop = '15px';
            errorDiv.style.padding = '10px';
            errorDiv.style.backgroundColor = '#fee2e2';
            errorDiv.style.borderRadius = '8px';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Mohon periksa kembali data Anda. Pastikan username, email, dan nomor telepon belum terdaftar.';
            registerForm.insertBefore(errorDiv, registerForm.querySelector('.register-btn'));
            
            setTimeout(() => {
                if (errorDiv.parentNode) errorDiv.remove();
            }, 5000);
            return;
        }
        
        // Disable button dan submit
        registerBtn.disabled = true;
        registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        registerForm.submit();
    });
    
    // Clear error on focus
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.classList.remove('error');
            const errorDiv = document.querySelector('.global-error');
            if (errorDiv) errorDiv.remove();
        });
    });
});
</script>
@endsection