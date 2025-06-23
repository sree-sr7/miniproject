document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const fields = ['username', 'password'];

    fields.forEach(field => {
        const input = document.getElementById(field);
        input.addEventListener('blur', () => validateField(field));
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            // Gather form data
            const formData = new FormData(form);

            // Send data using fetch()
            fetch('login.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(result => {
                console.log(result);
                alert(result);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error logging in!');
            });
        }
    });

    function validateField(field) {
        const input = document.getElementById(field);
        const errorElement = document.getElementById(`${field}Error`);
        let error = '';

        switch(field) {
            case 'username':
                if (input.value.length < 3) error = 'Username must be at least 3 characters long';
                break;
            case 'password':
                if (input.value.length < 8) error = 'Password must be at least 8 characters long';
                break;
        }

        errorElement.textContent = error;
        input.classList.toggle('error', error !== '');
        return error === '';
    }

    function validateForm() {
        let isValid = true;
        fields.forEach(field => {
            if (!validateField(field)) isValid = false;
        });
        return isValid;
    }
});
