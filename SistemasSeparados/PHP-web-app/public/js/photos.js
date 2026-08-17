function mostrarAlerta(msg, tipo = "erro") {
  const alerta = document.getElementById("alerta");
  if (!alerta || !msg) return;

  alerta.textContent = msg;
  alerta.className = "alerta show " + tipo;

  setTimeout(() => {
    alerta.classList.remove("show");
  }, 3000);
}

document.addEventListener("DOMContentLoaded", function () {
  const botaoTema = document.getElementById("toggleTema");
  const body = document.body;
  const fileInput = document.getElementById("fileInput");
  const preview = document.getElementById("preview");
  const alerta = document.getElementById("alerta");
  const btnMenu = document.getElementById("btn-menu");
  const menu = document.getElementById("menu");

  function aplicarTema(tema) {
    body.classList.remove("light", "dark");
    body.classList.add(tema);

    if (botaoTema) {
      botaoTema.textContent = tema === "dark" ? "Modo Claro" : "Modo Escuro";
    }

    localStorage.setItem("tema", tema);
  }

  if (botaoTema) {
    const temaSalvo = localStorage.getItem("tema") || "light";
    aplicarTema(temaSalvo);

    botaoTema.addEventListener("click", function () {
      const novoTema = body.classList.contains("dark") ? "light" : "dark";
      aplicarTema(novoTema);
    });
  }

  if (fileInput && preview) {
    fileInput.addEventListener("change", function (event) {
      const file = event.target.files[0];

      if (!file) {
        preview.src = "";
        preview.style.display = "none";
        return;
      }

      const reader = new FileReader();

      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = "block";
      };

      reader.readAsDataURL(file);
    });
  }

  if (alerta) {
    const msg = alerta.dataset.msg || "";
    const tipo = alerta.classList.contains("sucesso") ? "sucesso" : "erro";

    if (msg.trim() !== "") {
      mostrarAlerta(msg, tipo);
    }
  }

  if (btnMenu && menu) {
    btnMenu.addEventListener("click", function () {
      menu.classList.toggle("show");
    });
  }
});