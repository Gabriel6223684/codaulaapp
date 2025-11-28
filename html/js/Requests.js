class Requests {
    static form;
    static SetForm(id) {
        this.form = document.getElementById(id);
        if (!this.form) {
            throw new Error("O formulário não foi encontrado!");
        }
        return this;
    }
    static async Post(url) {
        const formData = new FormData(this.form);
        fetch('http://localhost/cliente/insert', {
            method: 'POST',
            body: JSON.stringify(dados),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const response = await fetch(url, option);
        return await response.json();
    }
}
export { Requests };