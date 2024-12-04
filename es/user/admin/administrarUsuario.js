document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const editButtons = document.querySelectorAll('.edit-btn');
    const viewButtons = document.querySelectorAll('.view-btn');
    const editModal = document.getElementById('editModal');
    const viewModal = document.getElementById('viewModal');
    const closeButtons = document.querySelectorAll('.close');
    const editForm = document.getElementById('editForm');
    const formFields = document.getElementById('formFields');
    const viewFields = document.getElementById('viewFields');
    const searchInput = document.getElementById('searchInput');
    const cancelEditButton = document.getElementById('cancelEdit');

    // Manejo de pestañas
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Manejo de edición
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-type');
            const id = button.getAttribute('data-id');
            openEditModal(type, id);
        });
    });

    // Manejo de visualización
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-type');
            const id = button.getAttribute('data-id');
            openViewModal(type, id);
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            editModal.style.display = 'none';
            viewModal.style.display = 'none';
        });
    });

    cancelEditButton.addEventListener('click', () => {
        editModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target == editModal || event.target == viewModal) {
            editModal.style.display = 'none';
            viewModal.style.display = 'none';
        }
    });

    editForm.addEventListener('submit', (e) => {
        e.preventDefault();
        editModal.style.display = 'none';
        location.reload(); // Recargar la página para mostrar todos los cambios
    });

    // Funcionalidad de búsqueda
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const activeTab = document.querySelector('.tab-content.active');
        const rows = activeTab.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if(text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    function openEditModal(type, id) {
        document.getElementById('editType').textContent = type;
        document.getElementById('editId').value = id;
        document.getElementById('editUserType').value = type;

        // Limpiar campos anteriores
        formFields.innerHTML = '';

        // Generar campos de formulario según el tipo de usuario
        switch(type) {
            case 'prospecto':
                createEditableField('nombre', 'Nombre', 'text', true);
                createEditableField('primerApellido', 'Primer Apellido', 'text', true);
                createEditableField('segundoApellido', 'Segundo Apellido', 'text', false);
                createEditableField('aniosExperiencia', 'Años de Experiencia', 'number', false);
                createEditableField('numTel', 'Teléfono', 'tel', true);
                createEditableField('resumen', 'Resumen', 'textarea', true);
                break;
            case 'empresa':
                createEditableField('nombre', 'Nombre de la Empresa', 'text', true);
                createEditableField('calle', 'Calle', 'text', true);
                createEditableField('numExterior', 'Número Exterior', 'text', true);
                createEditableField('colonia', 'Colonia', 'text', true);
                createEditableField('codigoPostal', 'Código Postal', 'text', true);
                createEditableField('nombreCont', 'Nombre de Contacto', 'text', true);
                break;
            case 'admin':
                createEditableField('correo', 'Correo', 'email', true);
                createEditableField('estado', 'Estado', 'select', true, ['activo', 'inactivo']);
                break;
        }

        // Cargar datos actuales del usuario
        fetch(`get_user.php?type=${type}&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    for (let key in data.user) {
                        const input = document.getElementById(key);
                        if (input) {
                            input.value = data.user[key];
                        }
                    }
                } else {
                    console.error('Error al cargar los datos del usuario');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

        editModal.style.display = 'block';
    }

    function createEditableField(name, label, type, required, options = []) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'form-group';

        const labelElement = document.createElement('label');
        labelElement.htmlFor = name;
        labelElement.textContent = label;
        fieldDiv.appendChild(labelElement);

        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'input-wrapper';

        let input;
        if (type === 'textarea') {
            input = document.createElement('textarea');
        } else if (type === 'select') {
            input = document.createElement('select');
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option.charAt(0).toUpperCase() + option.slice(1);
                input.appendChild(optionElement);
            });
        } else {
            input = document.createElement('input');
            input.type = type;
        }

        input.id = name;
        input.name = name;
        input.required = required;
        input.disabled = true;
        inputWrapper.appendChild(input);

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'edit-field-btn';
        editButton.textContent = 'Editar';
        editButton.onclick = () => toggleEdit(name);
        inputWrapper.appendChild(editButton);

        fieldDiv.appendChild(inputWrapper);

        const errorSpan = document.createElement('span');
        errorSpan.className = 'error-message';
        errorSpan.id = `${name}-error`;
        fieldDiv.appendChild(errorSpan);

        formFields.appendChild(fieldDiv);
    }

    function toggleEdit(fieldName) {
        const input = document.getElementById(fieldName);
        const button = input.nextElementSibling;

        if (input.disabled) {
            input.disabled = false;
            button.textContent = 'Guardar';
            button.className = 'save-field-btn';
            input.focus();
        } else {
            if (validateField(input)) {
                input.disabled = true;
                button.textContent = 'Editar';
                button.className = 'edit-field-btn';
                saveField(fieldName, input.value);
            }
        }
    }

    function validateField(input) {
        clearError(input);

        if (input.hasAttribute('required') && !input.value.trim()) {
            showError(input, 'Este campo es requerido');
            return false;
        }

        if (input.type === 'email' && input.value.trim() && !isValidEmail(input.value)) {
            showError(input, 'Por favor, ingrese un correo electrónico válido');
            return false;
        }

        if (input.type === 'tel' && input.value.trim() && !isValidPhone(input.value)) {
            showError(input, 'Por favor, ingrese un número de teléfono válido');
            return false;
        }

        if (input.id === 'aniosExperiencia' && input.value.trim() && parseInt(input.value) < 0) {
            showError(input, 'Los años de experiencia no pueden ser negativos');
            return false;
        }

        return true;
    }

    function saveField(fieldName, value) {
        const id = document.getElementById('editId').value;
        const userType = document.getElementById('editUserType').value;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('userType', userType);
        formData.append(fieldName, value);

        fetch('update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Campo ${fieldName} actualizado con éxito`);
            } else {
                console.error(`Error al actualizar el campo ${fieldName}: ${data.message}`);
                alert(`Error al actualizar el campo ${fieldName}: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Error al actualizar el campo ${fieldName}`);
        });
    }

    function showError(input, message) {
        const errorElement = document.getElementById(`${input.id}-error`);
        if (errorElement) {
            errorElement.textContent = message;
        }
        input.classList.add('error');
    }

    function clearError(input) {
        const errorElement = document.getElementById(`${input.id}-error`);
        if (errorElement) {
            errorElement.textContent = '';
        }
        input.classList.remove('error');
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function isValidPhone(phone) {
        const re = /^\+?[\d\s()-]{10,}$/;
        return re.test(phone);
    }

    function openViewModal(type, id) {
        document.getElementById('viewType').textContent = type;

        // Limpiar campos anteriores
        viewFields.innerHTML = 'Cargando...';

        // Obtener datos del usuario
        fetch(`get_user.php?type=${type}&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<dl>';
                    for (let key in data.user) {
                        let value = data.user[key];
                        // Formatear el contenido largo
                        if (typeof value === 'string' && value.length > 100) {
                            html += `<dt>${key}:</dt><dd>
                                <div class="long-content">${value}</div>
                                <button onclick="toggleLongContent(this.previousElementSibling)">Ver más</button>
                            </dd>`;
                        } else {
                            html += `<dt>${key}:</dt><dd>${value}</dd>`;
                        }
                    }
                    html += '</dl>';
                    viewFields.innerHTML = html;
                } else {
                    viewFields.innerHTML = 'Error al cargar los datos del usuario.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                viewFields.innerHTML = 'Error al cargar los datos del usuario.';
            });

        viewModal.style.display = 'block';
    }
});

function toggleLongContent(element) {
    element.classList.toggle('expanded');
    const button = element.nextElementSibling;
    if (element.classList.contains('expanded')) {
        button.textContent = 'Ver menos';
    } else {
        button.textContent = 'Ver más';
    }
}