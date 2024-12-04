// Variables de control para el estado de edición y paginación
let isEditing = false;
let currentRow = null;
let currentPage = 1;
const itemsPerPage = 10;

// Recuperar vacantes del localStorage al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("addVacancyModal");
    const btn = document.getElementById("addVacancyBtn");
    const span = document.getElementsByClassName("close")[0];
    const form = document.getElementById("vacancyForm");

    // Evento para abrir el modal al hacer clic en "Agregar Vacante"
    btn.onclick = function() {
        console.log("Modal abierto");  // Verificar que se abre el modal
        isEditing = false;  // Asegúrate de que está en modo agregar
        currentRow = null;  // Restablecer la fila actual
        form.reset();  // Restablecer el formulario
        document.getElementById("modalTitle").innerText = "Agregar Nueva Vacante"; // Establecer el título del modal
        document.getElementById("saveButton").innerText = "Guardar Vacante"; // Establecer el texto del botón a "Guardar Vacante"
        modal.style.display = "flex";  // Mostrar el modal
    };

    // Cerrar el modal al hacer clic en la "x"
    span.onclick = function() {
        modal.style.display = "none";
    };

    // Cerrar el modal al hacer clic fuera de él
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Manejo del envío del formulario
    form.onsubmit = function(event) {
        event.preventDefault();  // Evitar que se recargue la página
        agregarVacante(); // Llamar a la función para agregar la vacante
    };

    // Cargar vacantes al inicio
    cargarVacantes();
});

function cargarVacantes() {
    const savedVacantes = JSON.parse(localStorage.getItem("vacantes")) || [];
    console.log("Vacantes guardadas:", savedVacantes); // Verificar vacantes en localStorage
    mostrarPagina(currentPage, savedVacantes);  // Mostrar la página actual con las vacantes cargadas
    actualizarBotonesPrevNext(currentPage, savedVacantes.length);  // Actualizar botones de paginación
}

// Resto de tus funciones (agregarFilaVacante, redirigirPagina, mostrarPagina, etc.) aquí...

function agregarFilaVacante(title, status) {
    const newRow = document.createElement('tr');
    newRow.innerHTML = 
        `<td>${title}</td>
        <td><span>${status}</span></td>
        <td>
            <div class="actions">
                <button onclick="abrirModalEditar(this)">
                    <img src="https://img.icons8.com/ios/452/edit--v1.png" alt="Edit">
                </button>
                <button onclick="borrarVacante(this)">
                    <img src="https://img.icons8.com/ios/452/delete--v1.png" alt="Delete">
                </button>
                <button class="redirect-button" onclick="redirigirPagina()">
                    <img src="https://img.icons8.com/ios/452/external-link.png" alt="Redirect"> <!-- Ícono de enlace -->
                </button>
            </div>
        </td>`;
    document.getElementById("vacantesTableBody").appendChild(newRow);
}

// -------------------------------------------------------------------------------------------------------------------
// Función para manejar la redirección
function redirigirPagina() {
    window.location.href = "pagina_destino.html"; // Cambia "pagina_destino.html" a la URL deseada
}
// -------------------------------------------------------------------------------------------------------------------

function mostrarPagina(page, vacantes) {
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    document.getElementById("vacantesTableBody").innerHTML = "";  // Limpiar la tabla
    vacantes.slice(start, end).forEach(v => agregarFilaVacante(v.title, v.status));  // Mostrar las vacantes correspondientes

    actualizarControlesPaginacion(vacantes);  // Actualizar los controles de paginación
    actualizarBotonesPrevNext(page, vacantes.length);  // Actualizar botones de prev y next
}

function actualizarControlesPaginacion(vacantes) {
    const totalPages = Math.ceil(vacantes.length / itemsPerPage);  // Calcular total de páginas
    const pagination = document.getElementById("paginationControls");

    // Limpiar los botones de números de página, excepto los botones Prev y Next
    const buttons = pagination.querySelectorAll(".page-number");
    buttons.forEach(button => button.remove());

    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement("button");
        pageButton.innerText = i;
        pageButton.classList.add("page-number");
        if (i === currentPage) {
            pageButton.classList.add("active");
        }
        pageButton.onclick = () => {
            currentPage = i;
            mostrarPagina(currentPage, vacantes);  // Mostrar la página seleccionada
        };
        pagination.insertBefore(pageButton, document.getElementById("nextPageBtn"));  // Insertar antes del botón Next
    }
}

function actualizarBotonesPrevNext(page, totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);  // Calcular total de páginas
    const prevButton = document.getElementById("prevPageBtn");
    const nextButton = document.getElementById("nextPageBtn");

    prevButton.disabled = page === 1;  // Deshabilitar botón Prev si estamos en la primera página
    nextButton.disabled = page === totalPages;  // Deshabilitar botón Next si estamos en la última página

    prevButton.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            mostrarPagina(currentPage, JSON.parse(localStorage.getItem("vacantes")));  // Mostrar la página anterior
        }
    };

    nextButton.onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            mostrarPagina(currentPage, JSON.parse(localStorage.getItem("vacantes")));  // Mostrar la página siguiente
        }
    };
}

function guardarVacantes() {
    const rows = document.querySelectorAll("#vacantesTableBody tr");
    const vacantes = Array.from(rows).map(row => ({
        title: row.cells[0].innerText,
        status: row.cells[1].querySelector("span").innerText
    }));
    localStorage.setItem("vacantes", JSON.stringify(vacantes));  // Guardar vacantes en el localStorage
    actualizarControlesPaginacion(vacantes);  // Actualizar controles de paginación después de guardar
}

function agregarVacante() {
    const title = document.getElementById("vacancyTitle").value; // Toma el título del input
    const status = document.getElementById("vacancyStatus").value; // Toma el estado del input

    console.log("Título:", title);  // Verificar que se captura el título
    console.log("Estado:", status);  // Verificar que se captura el estado

    if (isEditing && currentRow) {
        // Si está en modo edición, actualiza la vacante
        currentRow.cells[0].innerText = title; // Actualiza el título en la tabla
        currentRow.cells[1].querySelector("span").innerText = status; // Actualiza el estado en la tabla

        // Actualiza el localStorage
        const vacantes = JSON.parse(localStorage.getItem("vacantes")) || [];
        const index = Array.from(document.querySelectorAll("#vacantesTableBody tr")).indexOf(currentRow);
        if (index > -1) {
            vacantes[index].title = title;
            vacantes[index].status = status;
            localStorage.setItem("vacantes", JSON.stringify(vacantes));
        }
        
        salirModoEdicion(); // Resetea el estado de edición
    } else {
        // Si no está en modo edición, agrega una nueva vacante
        const vacantes = JSON.parse(localStorage.getItem("vacantes")) || [];
        vacantes.push({ title, status }); // Agrega la nueva vacante
        localStorage.setItem("vacantes", JSON.stringify(vacantes)); // Actualiza el localStorage

        currentPage = Math.ceil(vacantes.length / itemsPerPage); // Ajusta la página actual
        mostrarPagina(currentPage, vacantes); // Muestra la tabla actualizada
        actualizarBotonesPrevNext(currentPage, vacantes.length); // Actualiza botones de paginación
    }

    cerrarModal(); // Cierra el modal
}

function abrirModalEditar(button) {
    const row = button.closest("tr");
    const title = row.cells[0].innerText;
    const status = row.cells[1].querySelector("span").innerText;

    document.getElementById("vacancyTitle").value = title; // Rellena el campo de título
    document.getElementById("vacancyStatus").value = status; // Rellena el campo de estado
    document.getElementById("modalTitle").innerText = "Editar Vacante"; // Cambia el título del modal
    document.getElementById("saveButton").innerText = "Editar Vacante"; // Cambia el texto del botón de guardar a "Editar Vacante"

    document.getElementById("addVacancyModal").style.display = "flex"; // Muestra el modal
    isEditing = true; // Establece el modo de edición
    currentRow = row; // Guarda la fila actual para poder editarla después
}

btn.onclick = function() {
    console.log("Modal abierto");  // Verificar que se abre el modal
    isEditing = false;
    currentRow = null;
    form.reset();
    document.getElementById("modalTitle").innerText = "Agregar Nueva Vacante"; // Restablece el título del modal
    document.getElementById("saveButton").innerText = "Guardar Vacante"; // Restablece el texto del botón a "Guardar Vacante"
    modal.style.display = "flex";
};

function borrarVacante(button) {
    // Buscar la fila en la que está el botón
    const row = button.closest("tr");

    // Eliminar del localStorage
    const vacantes = JSON.parse(localStorage.getItem("vacantes")) || [];
    const index = Array.from(document.querySelectorAll("#vacantesTableBody tr")).indexOf(row);

    // Verificar si el índice es válido y eliminarlo
    if (index > -1) {
        vacantes.splice(index, 1);  // Eliminar la vacante del array
        localStorage.setItem("vacantes", JSON.stringify(vacantes));  // Actualizar el localStorage
    }

    // Eliminar visualmente la fila
    row.remove();

    // Recargar la paginación y la tabla actualizada
    cargarVacantes();
}

// Lógica del modal y barra lateral
const modal = document.getElementById("addVacancyModal");
const btn = document.getElementById("addVacancyBtn");
const span = document.getElementsByClassName("close")[0];
const form = document.getElementById("vacancyForm");

// Evento para abrir el modal al hacer clic en "Agregar Vacante"
btn.onclick = function() {
    console.log("Modal abierto");  // Verificar que se abre el modal
    isEditing = false;  // Asegúrate de que está en modo agregar
    currentRow = null;  // Restablecer la fila actual
    form.reset();  // Restablecer el formulario
    document.getElementById("modalTitle").innerText = "Agregar Nueva Vacante"; // Establecer el título del modal
    document.getElementById("saveButton").innerText = "Guardar Vacante"; // Establecer el texto del botón a "Guardar Vacante"
    modal.style.display = "flex";  // Mostrar el modal
};

// Cerrar el modal al hacer clic en la "x"
span.onclick = function() {
    modal.style.display = "none";
}

// Cerrar el modal al hacer clic fuera de él
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Manejo del envío del formulario
form.onsubmit = function(event) {
    event.preventDefault();  // Evitar que se recargue la página

    const title = document.getElementById("vacancyTitle").value; // Obtener el título
    const status = document.getElementById("vacancyStatus").value; // Obtener el estado

    console.log("Título:", title);  // Verificar que se captura el título
    console.log("Estado:", status);  // Verificar que se captura el estado

    agregarVacante(); // Llamar a la función para agregar la vacante
}