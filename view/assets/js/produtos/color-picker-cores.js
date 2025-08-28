// Color Picker para página de cores
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('colorInput');
    const colorPickerBtn = document.getElementById('colorPickerBtn');
    const colorPickerContainer = document.getElementById('colorPickerContainer');
    
    let pickr = null;

    // Configuração do Pickr
    const pickrConfig = {
        el: colorPickerContainer,
        theme: 'nano',
        default: colorInput.value || '#128c7e',
        
        swatches: [
            'rgba(244, 67, 54, 1)',
            'rgba(233, 30, 99, 0.95)',
            'rgba(156, 39, 176, 0.9)',
            'rgba(103, 58, 183, 0.85)',
            'rgba(63, 81, 181, 0.8)',
            'rgba(33, 150, 243, 0.75)',
            'rgba(3, 169, 244, 0.7)',
            'rgba(0, 188, 212, 0.7)',
            'rgba(0, 150, 136, 0.75)',
            'rgba(76, 175, 80, 0.8)',
            'rgba(139, 195, 74, 0.85)',
            'rgba(205, 220, 57, 0.9)',
            'rgba(255, 235, 59, 0.95)',
            'rgba(255, 193, 7, 1)',
            'rgba(255, 152, 0, 1)',
            'rgba(255, 87, 34, 1)',
            'rgba(121, 85, 72, 1)',
            'rgba(158, 158, 158, 1)',
            'rgba(96, 125, 139, 1)',
            'rgba(0, 0, 0, 1)',
            'rgba(255, 255, 255, 1)'
        ],

        components: {
            preview: true,
            opacity: true,
            hue: true,

            interaction: {
                hex: true,
                rgba: true,
                hsva: true,
                input: true,
                clear: true,
                save: true
            },
            strings: {
                save: 'Salvar',
                clear: 'Limpar'
            }
        }
    };

    // Inicializar Pickr
    function initPickr() {
        if (pickr) {
            pickr.destroyAndRemove();
        }

        pickr = new Pickr(pickrConfig);

        // Eventos do Pickr
        pickr.on('init', instance => {
            console.log('Color picker inicializado');
        }).on('save', (color, instance) => {
            const hexColor = color.toHEXA().toString();
            colorInput.value = hexColor;
            colorInput.style.backgroundColor = hexColor;
            colorInput.style.color = getContrastColor(hexColor);
            pickr.hide();
        }).on('change', (color, source, instance) => {
            const hexColor = color.toHEXA().toString();
            colorInput.style.backgroundColor = hexColor;
            colorInput.style.color = getContrastColor(hexColor);
        }).on('clear', instance => {
            colorInput.value = '';
            colorInput.style.backgroundColor = '';
            colorInput.style.color = '';
            pickr.hide();
        });
    }

    // Função para determinar cor de contraste
    function getContrastColor(hexColor) {
        // Remove o # se presente
        hexColor = hexColor.replace('#', '');
        
        // Converte para RGB
        const r = parseInt(hexColor.substr(0, 2), 16);
        const g = parseInt(hexColor.substr(2, 2), 16);
        const b = parseInt(hexColor.substr(4, 2), 16);
        
        // Calcula luminância
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        
        // Retorna branco ou preto baseado na luminância
        return luminance > 0.5 ? '#000000' : '#ffffff';
    }

    // Evento do botão do color picker
    colorPickerBtn.addEventListener('click', function() {
        if (!pickr) {
            initPickr();
        }
        pickr.show();
    });

    // Evento de mudança no input manual
    colorInput.addEventListener('input', function() {
        let value = this.value;
        
        // Adiciona # se não presente
        if (value && !value.startsWith('#')) {
            value = '#' + value;
            this.value = value;
        }
        
        // Valida formato hexadecimal
        if (/^#[0-9A-F]{6}$/i.test(value)) {
            this.style.backgroundColor = value;
            this.style.color = getContrastColor(value);
        } else {
            this.style.backgroundColor = '';
            this.style.color = '';
        }
    });

    // Evento de foco no input
    colorInput.addEventListener('focus', function() {
        if (this.value && /^#[0-9A-F]{6}$/i.test(this.value)) {
            this.style.backgroundColor = this.value;
            this.style.color = getContrastColor(this.value);
        }
    });

    // Evento de blur no input
    colorInput.addEventListener('blur', function() {
        // Remove # se o usuário não digitou um hex válido
        if (this.value && !/^#[0-9A-F]{6}$/i.test(this.value)) {
            this.value = '';
            this.style.backgroundColor = '';
            this.style.color = '';
        }
    });

    // Inicializar com valor existente
    if (colorInput.value && /^#[0-9A-F]{6}$/i.test(colorInput.value)) {
        colorInput.style.backgroundColor = colorInput.value;
        colorInput.style.color = getContrastColor(colorInput.value);
    }
}); 