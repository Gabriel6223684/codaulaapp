class Validate {
    static form;
    static SetForm(id) {
        this.form = document.getElementById(id);
        if (!this.form) {
            throw new Error("Formulário não encontrado!");
        }
        return this;
    }
    static Validate() {
        //Selecionae todos os campos, input do form
        const inputs = this.form.querySelectorAll('input, textarea, select');
        //percorre todos os campos do forma
        inputs.forEach(input => {            
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (!input.checkValidity()) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        });
    }
}
export { Validate };