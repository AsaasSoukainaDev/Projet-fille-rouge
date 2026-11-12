const form = document.getElementById("form-{{entityLower}}");
const sectionForm = document.getElementById("section-form-{{entityLower}}");
const btnAjouter = document.getElementById("btn-show-form-{{entityLower}}");
const btnAnnuler = document.getElementById("btn-cancel-form-{{entityLower}}");
const tbody = document.getElementById("table-{{entitiesLower}}-body");
const btnEnregistrer = document.getElementById("enregistrer-{{entityLower}}");
const api = "backend/api/{{apiEndpoint}}.php";

function Loading(isLoading) {
    if (isLoading) {
        btnEnregistrer.disabled = true;
        btnEnregistrer.textContent = "En cours ...";
    } else {
        btnEnregistrer.disabled = false;
        btnEnregistrer.textContent = "Enregistrer";
    }
}

function Toast(message, type = "success") {
    const toastContainer = document.getElementById("toast-container");
    if (!toastContainer) return;
    const toast = document.createElement("div");
    const color = type === "success" ? "bg-green-500" : "bg-red-500";

    toast.className = `${color} px-5 py-4 text-white rounded shadow-md transition-opacity duration-500`;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, 3000);
}

function Afficher() {
    fetch(api)
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = "";
            data.forEach(item => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="p-4 text-sm text-gray-700">${item["id"]}</td>
{{tableRowColumns}}
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error(error);
            Toast("Erreur lors du chargement des données", "failed");
        });
}

document.addEventListener("DOMContentLoaded", () => {
    Afficher();

    if (form) {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            Loading(true);

            const payload = {
{{formPayloadMappings}}
            };

            fetch(api, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                Loading(false);
                if (!data.success) {
                    throw new Error(data.message);
                }
                Toast(data.message, "success");
                form.reset();
                sectionForm.classList.add("hidden");
                Afficher();
            })
            .catch(error => {
                Loading(false);
                Toast(error.message, "failed");
            });
        });
    }
});

if (btnAjouter && sectionForm) {
    btnAjouter.addEventListener("click", () => sectionForm.classList.remove("hidden"));
}

if (btnAnnuler && sectionForm) {
    btnAnnuler.addEventListener("click", () => sectionForm.classList.add("hidden"));
}

