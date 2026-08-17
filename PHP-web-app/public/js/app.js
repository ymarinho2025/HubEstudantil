function mostrarAlerta(msg, tipo = "erro") {
  const alerta = document.getElementById("alerta");
  if (!alerta) return;

  alerta.textContent = msg;
  alerta.className = "alerta show " + tipo;

  setTimeout(() => alerta.classList.remove("show"), 2500);
}

function voltar() {
  window.history.back();
}

function logout() {
  localStorage.removeItem("user");
  window.location.href = "login.html";
}

/* ================= TEMA ================= */

const botao = document.getElementById("toggleTema");

function aplicarTema(tema) {
  document.body.className = tema;

  if (botao) {
    botao.textContent = tema === "dark" ? "☀️" : "🌙";
  }

  localStorage.setItem("tema", tema);
}

if (botao) {
  botao.onclick = () => {
    const novo = document.body.classList.contains("dark") ? "light" : "dark";
    aplicarTema(novo);
  };
}

window.addEventListener("load", () => {
  const tema = localStorage.getItem("tema") || "light";
  aplicarTema(tema);

  const meses = ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"];

  const m1 = document.getElementById("mesUpload");
  const m2 = document.getElementById("mesFiltro");

  meses.forEach(m => {
    if (m1) m1.innerHTML += `<option>${m}</option>`;
    if (m2) m2.innerHTML += `<option>${m}</option>`;
  });

  for (let i = 2026; i <= 2060; i++) {
    if (anoUpload) anoUpload.innerHTML += `<option>${i}</option>`;
    if (anoFiltro) anoFiltro.innerHTML += `<option>${i}</option>`;
  }
});

/* ================= FOTOS ================= */

let fotos = JSON.parse(localStorage.getItem("fotos")) || [];

function salvarStorage() {
  localStorage.setItem("fotos", JSON.stringify(fotos));
}

function previewImagem(e) {
  const file = e.target.files[0];
  const preview = document.getElementById("preview");

  if (!file) return;

  const reader = new FileReader();
  reader.onload = ev => {
    preview.src = ev.target.result;
    preview.style.display = "block";
  };
  reader.readAsDataURL(file);
}

function salvarFoto() {
  const file = fileInput.files[0];

  if (!file) return mostrarAlerta("Escolha uma imagem!");

  const reader = new FileReader();

  reader.onload = e => {
    fotos.push({
      url: e.target.result,
      mes: mesUpload.value,
      ano: parseInt(anoUpload.value)
    });

    salvarStorage();
    mostrarAlerta("Foto salva!", "sucesso");
  };

  reader.readAsDataURL(file);
}

function filtrar() {
  const galeria = document.getElementById("galeria");
  galeria.innerHTML = "";

  const resultado = fotos.filter(f =>
    f.mes === mesFiltro.value &&
    f.ano === parseInt(anoFiltro.value)
  );

  if (!resultado.length) {
    mostrarAlerta("Nenhuma foto 😢");
    return;
  }

  resultado.forEach((foto, i) => {
    const div = document.createElement("div");
    div.className = "foto-item";

    div.innerHTML = `
      <img src="${foto.url}">
      <button class="btn-remover">×</button>
    `;

    div.querySelector("button").onclick = () => {
      fotos.splice(i, 1);
      salvarStorage();
      filtrar();
    };

    galeria.appendChild(div);
  });
}