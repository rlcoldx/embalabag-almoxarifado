// Funções globais para controles rápidos
window.selectAllCargos = function() {
    const checkboxes = document.querySelectorAll('.cargo-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateCargosCount();
};

window.deselectAllCargos = function() {
    const checkboxes = document.querySelectorAll('.cargo-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateCargosCount();
};

window.updateCargosCount = function() {
    const checkboxes = document.querySelectorAll('.cargo-checkbox:checked');
    const countElement = document.getElementById('cargosCount');
    if (countElement) {
        countElement.textContent = `${checkboxes.length} cargo${checkboxes.length !== 1 ? 's' : ''} selecionado${checkboxes.length !== 1 ? 's' : ''}`;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    
    let DOMAIN = document.body.getAttribute('data-domain') || '';
    // Controle de exibição dos campos específicos baseado no tipo de usuário
    const tipoSelect = document.getElementById('tipo');
    const companhiaFields = document.getElementById('companhiaFields');
    const cargosFields = document.getElementById('cargosFields');
    const controlesRapidos = document.getElementById('controlesRapidos');

    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            
            // Esconder todos os campos específicos
            companhiaFields.style.display = 'none';
            cargosFields.style.display = 'none';
            controlesRapidos.style.display = 'none';
            
            // Mostrar campos baseado na seleção
            if (selectedValue === '3') {
                companhiaFields.style.display = 'block';
            } else if (selectedValue === '2') {
                cargosFields.style.display = 'block';
                controlesRapidos.style.display = 'block';
            }
        });
    }

    // Contador de cargos selecionados
    const cargoCheckboxes = document.querySelectorAll('.cargo-checkbox');
    cargoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCargosCount);
    });

    // Inicializar contador
    updateCargosCount();

    // Formulário de edição
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner me-2 align-middle d-inline-block fa-spin"></i>Atualizando...';
            submitBtn.disabled = true;

            // Coletar dados do formulário
            const formData = new FormData(this);
            const userId = this.getAttribute('data-user-id');
            
            // Fazer requisição AJAX
            fetch(`${DOMAIN}/users/update/${userId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message || 'Usuário atualizado com sucesso!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = `${DOMAIN}/users`;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || 'Erro ao atualizar usuário.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro de conexão. Tente novamente.',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Restaurar botão
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Máscara para telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length === 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length === 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 6) {
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length >= 2) {
                    value = value.replace(/(\d{2})(\d{0,4})/, '($1) $2');
                }
            }
            e.target.value = value;
        });
    }

    // Máscara para CNPJ
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4');
                value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
                value = value.replace(/(\d{2})(\d{3})/, '$1.$2');
            }
            e.target.value = value;
        });
    }

    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
            }
            e.target.value = value;
        });

        // Busca automática de endereço pelo CEP
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep !== '' && cep.length === 8) {
                // Mostrar loading
                this.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' fill=\'%23adb5bd\' class=\'bi bi-search\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z\'/%3E%3C/svg%3E")';
                this.style.backgroundRepeat = 'no-repeat';
                this.style.backgroundPosition = 'right 0.75rem center';
                this.style.backgroundSize = '16px 12px';

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            document.getElementById('uf').value = data.uf || '';
                            
                            // Focar no campo número se logradouro foi preenchido
                            if (data.logradouro) {
                                document.getElementById('numero').focus();
                            }
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'CEP não encontrado',
                                text: 'O CEP informado não foi encontrado. Verifique e tente novamente.',
                                confirmButtonText: 'OK'
                            });
                            limparCamposEndereco();
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro na busca',
                            text: 'Erro ao buscar o CEP. Verifique sua conexão e tente novamente.',
                            confirmButtonText: 'OK'
                        });
                        limparCamposEndereco();
                    })
                    .finally(() => {
                        // Remover loading
                        this.style.backgroundImage = '';
                    });
            } else if (cep !== '' && cep.length !== 8) {
                Swal.fire({
                    icon: 'warning',
                    title: 'CEP inválido',
                    text: 'O CEP deve ter 8 dígitos.',
                    confirmButtonText: 'OK'
                });
                limparCamposEndereco();
            }
        });
    }

    // Função para limpar campos de endereço
    function limparCamposEndereco() {
        document.getElementById('logradouro').value = '';
        document.getElementById('bairro').value = '';
        document.getElementById('cidade').value = '';
        document.getElementById('uf').value = '';
    }

    // Validação de senha
    const senhaInput = document.getElementById('senha');
    if (senhaInput) {
        senhaInput.addEventListener('input', function(e) {
            const value = e.target.value;
            const minLength = 6;
            
            if (value.length > 0 && value.length < minLength) {
                this.setCustomValidity(`A senha deve ter pelo menos ${minLength} caracteres`);
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Validação de email
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function(e) {
            const value = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                this.setCustomValidity('Digite um email válido');
            } else {
                this.setCustomValidity('');
            }
        });
    }
}); 