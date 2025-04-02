const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

const db = mysql.createConnection({
    host: '127.0.0.1',
    user: 'Janco',
    password: 'ZG3011#cdz', // tu contraseña de MySQL
    database: 'bd_helios'
});

db.connect(err => {
    if (err) throw err;
    console.log('Connected to database.');
});

app.post('/login', (req, res) => {
    const { email, password } = req.body;

    const query = 'SELECT * FROM user WHERE email_user = ? AND pass_user = ?';
    db.query(query, [email, password], (err, results) => {
        if (err) {
            return res.status(500).json({ status: 'error', message: 'Server error' });
        }
        if (results.length > 0) {
            // Login exitoso
            res.json({ status: 'success' });
        } else {
            // Credenciales incorrectas
            res.json({ status: 'error', message: 'Email o contraseña incorrectos' });
        }
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
