/**
 * Scanner de código de barras: câmera (BarcodeDetector) + leitor USB / digitação
 */

(function () {
    let stream = null;
    let detectTimer = null;
    let tipoAtual = 'movimentacao';
    let modalInstance = null;
    let applying = false;

    function getModalEl() {
        return document.getElementById('modalScanner');
    }

    function getModal() {
        const el = getModalEl();
        if (!el || typeof bootstrap === 'undefined') {
            return null;
        }
        modalInstance = bootstrap.Modal.getOrCreateInstance(el);
        return modalInstance;
    }

    function stopCamera() {
        if (detectTimer) {
            clearTimeout(detectTimer);
            detectTimer = null;
        }
        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
            stream = null;
        }
        const video = document.getElementById('scannerVideo');
        if (video) {
            video.srcObject = null;
            video.classList.add('d-none');
        }
    }

    function setHint(text) {
        const hint = document.getElementById('scannerCameraHint');
        if (hint) {
            hint.textContent = text;
        }
    }

    async function detectLoop(video, detector) {
        if (!stream || applying) {
            return;
        }

        try {
            const codes = await detector.detect(video);
            if (codes && codes.length && codes[0].rawValue) {
                aplicarCodigo(codes[0].rawValue.trim());
                return;
            }
        } catch (e) {
            // frame inválido, tenta de novo
        }

        detectTimer = setTimeout(function () {
            detectLoop(video, detector);
        }, 250);
    }

    async function startCamera() {
        const video = document.getElementById('scannerVideo');
        if (!video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setHint('Câmera indisponível. Use um leitor USB ou digite o código.');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } }
            });
            video.srcObject = stream;
            video.classList.remove('d-none');
            await video.play();

            if ('BarcodeDetector' in window) {
                const detector = new BarcodeDetector({
                    formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'code_93', 'codabar', 'qr_code', 'upc_a', 'upc_e']
                });
                setHint('Aponte a câmera para o código de barras.');
                detectLoop(video, detector);
            } else {
                setHint('Câmera ativa. Este navegador não lê o código automaticamente — use o leitor USB ou digite o código.');
            }
        } catch (e) {
            setHint('Câmera indisponível. Use um leitor USB ou digite o código.');
        }
    }

    function aplicarCodigo(codigo) {
        if (!codigo || applying) {
            return;
        }
        applying = true;
        stopCamera();
        const modal = getModal();
        if (modal) {
            modal.hide();
        }
        if (typeof window.aplicarCodigoScanner === 'function') {
            window.aplicarCodigoScanner(codigo, tipoAtual);
        }
        applying = false;
    }

    window.abrirScanner = function (tipo) {
        tipoAtual = tipo || 'movimentacao';
        applying = false;
        const input = document.getElementById('scannerInput');
        if (input) {
            input.value = '';
        }
        setHint('Preparando o scanner...');
        const modal = getModal();
        if (modal) {
            modal.show();
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire('Scanner', 'Modal do scanner não encontrado nesta página.', 'warning');
        }
    };

    window.confirmarCodigoScanner = function (codigo, tipo) {
        if (tipo) {
            tipoAtual = tipo;
        }
        const value = (typeof codigo === 'string' ? codigo : (document.getElementById('scannerInput') || {}).value || '').trim();
        if (!value) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Atenção', 'Digite ou escaneie um código.', 'warning');
            }
            return;
        }
        aplicarCodigo(value);
    };

    document.addEventListener('DOMContentLoaded', function () {
        const el = getModalEl();
        if (!el) {
            return;
        }

        el.addEventListener('shown.bs.modal', function () {
            const input = document.getElementById('scannerInput');
            if (input) {
                input.focus();
            }
            startCamera();
        });

        el.addEventListener('hidden.bs.modal', function () {
            stopCamera();
        });

        const input = document.getElementById('scannerInput');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.confirmarCodigoScanner(input.value.trim());
                }
            });
        }

        const btn = document.getElementById('btnConfirmarScanner');
        if (btn) {
            btn.addEventListener('click', function () {
                window.confirmarCodigoScanner();
            });
        }

        document.querySelectorAll('[data-scanner-tipo]').forEach(function (botao) {
            botao.addEventListener('click', function () {
                window.abrirScanner(botao.getAttribute('data-scanner-tipo'));
            });
        });
    });
})();
