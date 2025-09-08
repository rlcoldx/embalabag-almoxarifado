// Script para página de criar cargo

// Funções para controles rápidos (escopo global)
window.selectAllPermissions = function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateModuleCheckboxes();
    updatePermissionCount();
}

window.deselectAllPermissions = function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateModuleCheckboxes();
    updatePermissionCount();
}

window.selectReadOnly = function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        // Buscar o label de forma mais segura
        const label = checkbox.closest('.form-check-label') || checkbox.nextElementSibling;
        if (label) {
            const badge = label.querySelector('.badge');
            if (badge) {
                const action = badge.textContent.trim().toLowerCase();
                checkbox.checked = action === 'visualizar';
            }
        }
    });
    updateModuleCheckboxes();
    updatePermissionCount();
}

window.initializePermissionControls = function() {
    // Adicionar event listeners para checkboxes de módulos
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.getAttribute('data-module');
            const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
            permissionCheckboxes.forEach(permissionCheckbox => {
                permissionCheckbox.checked = this.checked;
            });
            updatePermissionCount();
        });
    });

    // Adicionar event listeners para checkboxes de permissões
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateModuleCheckboxes();
            updatePermissionCount();
        });
    });

    // Inicializar contador
    updatePermissionCount();
}

window.updateModuleCheckboxes = function() {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(moduleCheckbox => {
        const module = moduleCheckbox.getAttribute('data-module');
        const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
        const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:checked`);
        
        // Marcar checkbox do módulo se todas as permissões estiverem marcadas
        moduleCheckbox.checked = checkedPermissions.length === permissionCheckboxes.length && permissionCheckboxes.length > 0;
        
        // Adicionar classe indeterminate se algumas permissões estiverem marcadas
        if (checkedPermissions.length > 0 && checkedPermissions.length < permissionCheckboxes.length) {
            moduleCheckbox.indeterminate = true;
        } else {
            moduleCheckbox.indeterminate = false;
        }
    });
}

window.updatePermissionCount = function() {
    const checkedPermissions = document.querySelectorAll('.permission-checkbox:checked');
    const countElement = document.getElementById('permissionsCount');
    if (countElement) {
        countElement.textContent = `${checkedPermissions.length} permissões selecionadas`;
        
        // Atualizar cor do badge baseado na quantidade
        countElement.className = 'badge';
        if (checkedPermissions.length === 0) {
            countElement.classList.add('bg-secondary-transparent');
        } else if (checkedPermissions.length <= 5) {
            countElement.classList.add('bg-info-transparent');
        } else if (checkedPermissions.length <= 10) {
            countElement.classList.add('bg-warning-transparent');
        } else {
            countElement.classList.add('bg-success-transparent');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Formulário de criar cargo
    const createCargoForm = document.getElementById('createCargoForm');
    if (createCargoForm) {
        createCargoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
                        
            const url = buildUrl('/cargos/store');
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.error
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao criar cargo'
                });
            });
        });
    }

    // Inicializar contadores e controles
    initializePermissionControls();
}); 