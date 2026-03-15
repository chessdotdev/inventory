<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .register-header i {
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            outline: none;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 13px;
        }

        .password-requirements h6 {
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }

        .password-requirements ul {
            padding-left: 20px;
            margin: 0;
        }

        .password-requirements li {
            margin-bottom: 5px;
            color: #666;
        }

        .password-requirements li.valid {
            color: #28a745;
            position: relative;
        }

        .password-requirements li.valid::before {
            content: "✓ ";
            color: #28a745;
        }

        .password-requirements li.invalid {
            color: #dc3545;
            position: relative;
        }

        .password-requirements li.invalid::before {
            content: "✗ ";
            color: #dc3545;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 14px;
        }

        .form-control-wrapper {
            position: relative;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 8px;
        }

        .loading {
            display: none;
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-user-shield"></i> Admin Registration</h2>
        </div>

        <div id="message"></div>

        <form id="registerForm">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="form-control-wrapper">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <span class="toggle-password" id="togglePassword"><i class="fas fa-eye"></i></span>
                </div>
                <div class="password-requirements" id="passwordRequirements" style="display: none;">
                    <h6>Password Requirements:</h6>
                    <ul>
                        <li id="lengthReq">At least 8 characters</li>
                        <li id="uppercaseReq">At least one uppercase letter</li>
                        <li id="lowercaseReq">At least one lowercase letter</li>
                        <li id="numberReq">At least one number</li>
                        <li id="specialReq">At least one special character (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn-register" id="registerBtn">
                <span id="btnText">Register</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm loading" role="status" aria-hidden="true"></span>
            </button>
        </form>

        <div class="login-link">
            Already have an admin account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const messageDiv = document.getElementById('message');
        const registerBtn = document.getElementById('registerBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const togglePassword = document.getElementById('togglePassword');
        const passwordRequirements = document.getElementById('passwordRequirements');
        const lengthReq = document.getElementById('lengthReq');
        const uppercaseReq = document.getElementById('uppercaseReq');
        const lowercaseReq = document.getElementById('lowercaseReq');
        const numberReq = document.getElementById('numberReq');
        const specialReq = document.getElementById('specialReq');

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Show password requirements when password field is focused
        passwordInput.addEventListener('focus', function() {
            passwordRequirements.style.display = 'block';
        });

        // Hide password requirements when password field is blurred and empty
        passwordInput.addEventListener('blur', function() {
            if (this.value === '') {
                passwordRequirements.style.display = 'none';
            }
        });

        // Validate password as user types
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Check length
            if (password.length >= 8) {
                lengthReq.className = 'valid';
            } else {
                lengthReq.className = 'invalid';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                uppercaseReq.className = 'valid';
            } else {
                uppercaseReq.className = 'invalid';
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                lowercaseReq.className = 'valid';
            } else {
                lowercaseReq.className = 'invalid';
            }
            
            // Check number
            if (/[0-9]/.test(password)) {
                numberReq.className = 'valid';
            } else {
                numberReq.className = 'invalid';
            }
            
            // Check special character
            if (/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/.test(password)) {
                specialReq.className = 'valid';
            } else {
                specialReq.className = 'invalid';
            }
        });

        // Validate confirm password as user types
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate form
            if (username.length < 3) {
                showMessage('Username must be at least 3 characters long', 'danger');
                return;
            }
            
            if (email.length < 6) {
                showMessage('Email must be at least 6 characters long', 'danger');
                return;
            }
            
            if (password.length < 8) {
                showMessage('Password must be at least 8 characters long', 'danger');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('Passwords do not match', 'danger');
                return;
            }
            
            // Show loading state
            registerBtn.disabled = true;
            btnText.textContent = 'Registering...';
            btnSpinner.classList.remove('loading');
            
            try {
                const response = await fetch('admin_register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirmPassword=${encodeURIComponent(confirmPassword)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message || 'Admin account created successfully', 'success');
                    // Reset form
                    form.reset();
                } else {
                    showMessage(data.message || 'Registration failed', 'danger');
                }
            } catch (error) {
                showMessage('Error: ' + error.message, 'danger');
            } finally {
                registerBtn.disabled = false;
                btnText.textContent = 'Register';
                btnSpinner.classList.add('loading');
            }
        });

        function showMessage(message, type) {
            messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> -->