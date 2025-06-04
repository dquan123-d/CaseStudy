document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Form validation for register page
    const registerForm = document.querySelector('.auth-form');
    if (registerForm && window.location.pathname.includes('register')) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('register-password');
            const confirmPassword = document.getElementById('register-confirm-password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Mật khẩu và xác nhận mật khẩu không khớp!');
                confirmPassword.focus();
            }
            
            // Additional validation can be added here
        });
    }
    
    // Auto-focus first input field
    const firstInput = document.querySelector('.auth-form input');
    if (firstInput) {
        firstInput.focus();
    }
});