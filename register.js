document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const responseDiv = document.getElementById('response');
        if (data.status === 'success') {
            responseDiv.innerHTML = '<p>Registro exitoso</p>';
            document.getElementById('registerForm').reset();
        } else {
            responseDiv.innerHTML = `<p>Error: ${data.message}</p>`;
        }
    })
    .catch(error => console.error('Error:', error));
});
