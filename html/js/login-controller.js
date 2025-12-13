const nodemailer = require("nodemailer");

// CONFIGURAÇÃO DO SERVIDOR DE E-MAIL
const transporter = nodemailer.createTransport({
    host: "smtp.seudominio.com",
    port: 587,
    secure: false,
    auth: {
        user: "seu-email@seudominio.com",
        pass: "SUA-SENHA"
    }
});

// FUNÇÃO DE LOGIN
exports.login = async (req, res) => {
    const { login, senha } = req.body;

    // AQUI você faz a validação no banco:
    // const usuario = await Usuario.findOne({ email: login });

    const usuarioFalso = { nome: 'Usuário Teste', email: login }; // EXEMPLO
    
    if (!usuarioFalso) {
        return res.status(401).send("Usuário não encontrado");
    }

    // supondo que a senha esteja correta...

    // ENVIA O E-MAIL DE LOGIN
    const mailOptions = {
        from: "Sistema <seu-email@seudominio.com>",
        to: usuarioFalso.email,
        subject: "Novo acesso realizado!",
        html: `
            <h2>Olá, ${usuarioFalso.nome}!</h2>
            <p>Um novo login foi realizado em sua conta.</p>
            <p><b>Se não foi você</b>, recomendamos alterar sua senha imediatamente.</p>
            <br>
            <small>Mensagem automática - Não responda.</small>
        `
    };

    transporter.sendMail(mailOptions, (error, info) => {
        if (error) {
            console.error("Erro ao enviar e-mail:", error);
        }
        console.log("E-mail enviado:", info.response);
    });

    // REDIRECIONA OU RETORNA SUCESSO
    return res.redirect("/painel");
};

const express = require('express');
const app = express();
const loginRouter = require('./login-controller');

app.use(express.json());
app.use(express.static('public'));
app.use(loginRouter);

app.listen(3000, () => console.log('Server rodando na porta 3000'));
