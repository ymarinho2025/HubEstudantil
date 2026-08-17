document.getElementById("registerForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = this;
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (!name || !email || !password) {
        alert("Por favor, preencha todos os campos!");
        return;
    }

    if (name.length < 2 || name.length > 50) {
        alert("O nome deve conter entre 2 e 50 caracteres!");
        return;
    }

    if (email.length > 100) {
        alert("O email deve conter no máximo 100 caracteres!");
        return;
    }

    if (password.length < 8) {
        alert("A senha deve conter no mínimo 8 caracteres!");
        return;
    }

    const nameRegex = /^[\p{L}\s.'-]+$/u;
    if (!nameRegex.test(name)) {
        alert("O nome contém caracteres inválidos!");
        return;
    }

    if (/[<>]/.test(name) || /[<>]/.test(email)) {
        alert("A entrada contém caracteres inválidos!");
        return;
    }

    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        alert("O email é inválido!");
        return;
    }

    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        if (response.status === 200) {
            alert("Usuário registrado com sucesso!");
            window.location.href = "login.php";
            return;
        }

        if (response.status === 400) {
            alert("Dados inválidos.");
            return;
        }

        if (response.status === 405) {
            alert("Método não permitido.");
            return;
        }

        if (response.status === 409) {
            alert("Email já cadastrado.");
            return;
        }

        if (response.status === 500) {
            alert("Erro interno no servidor.");
            return;
        }

        alert(`Erro inesperado. Código HTTP: ${response.status}`);
    } catch (error) {
        alert("Não foi possível conectar ao servidor. Verifique sua conexão ou tente novamente.");
        console.error("Erro na requisição:", error);
    }
});