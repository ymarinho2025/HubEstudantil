document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#userTableBody tr');

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const email = row.getAttribute('data-email') || '';

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function abrirModalEditar(id, nome, role) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nome").value = nome;
    document.getElementById("edit_role").value = role;
    document.getElementById("modalEditar").style.display = "flex";
}

function fecharModalEditar() {
    document.getElementById("modalEditar").style.display = "none";
}

window.addEventListener("click", function(e) {
    const modal = document.getElementById("modalEditar");
    if (e.target === modal) {
        fecharModalEditar();
    }
});