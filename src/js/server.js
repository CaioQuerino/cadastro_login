fetch('login.php', {
    method: 'POST',
    body: new FormData(document.getElementById('login-form'))
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        // Show success message
        alert(data.message);
        // Redirect after 1 second
        setTimeout(() => {
            window.location.href = data.redirect;
        }, 1000);
    } else {
        // Show error message
        alert(data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
    alert('Ocorreu um erro durante o login');
});