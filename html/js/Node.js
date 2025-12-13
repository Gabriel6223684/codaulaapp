const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const crypto = require('crypto');

const app = express();
app.use(bodyParser.json());

app.post('/recuperar-senha', async (req, res) => {
    const { email } = req.body;

    if (!email) return res.json({ message: 'Informe um e-mail válido.' });

    // Aqui você buscaria o usuário no banco
    const usuario = { nome: 'Teste', email: 'teste@exemplo.com' };

    // Gerar token temporário
    const token = crypto.randomBytes(20).toString('hex');

    // Configurar transporte SMTP
    const transporter = nodemailer.createTransport({
        host: 'smtp.seuprovedor.com',
        port: 587,
        auth: { user: 'seu-email@provedor.com', pass: 'sua-senha' }
    });

    const mailOptions = {
        from: '"Admin" <seu-email@provedor.com>',
        to: email,
        subject: 'Recuperação de senha',
        text: `Olá ${usuario.nome},\nClique no link para redefinir sua senha:\nhttp://seusite.com/redefinir-senha?token=${token}`
    };

    try {
        await transporter.sendMail(mailOptions);
        res.json({ message: 'Se o e-mail existir, você receberá instruções para recuperar a senha.' });
    } catch (err) {
        console.error(err);
        res.status(500).json({ message: 'Erro ao enviar o e-mail.' });
    }
});

app.listen(3000, () => console.log('Servidor rodando na porta 3000'));
