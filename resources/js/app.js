/**
 * Comportamiento de la biblioteca de diseños de reconocimientos.
 * Se mantiene fuera del atributo x-data para evitar que las comillas del
 * contenido de SweetAlert rompan el HTML generado por Blade.
 */
window.reconocimientoDisenos = () => ({
    dragging: false,
    fileName: '',
    uploadProgress: 0,
    uploading: false,
    search: '',
    status: 'todos',

    openFilePicker() {
        this.$refs.designInput?.click();
    },

    fileSelected(event) {
        const file = event.target.files?.[0];

        if (!file) {
            this.fileName = '';
            return;
        }

        if (!this.validarArchivo(file)) {
            event.target.value = '';
            this.fileName = '';
            return;
        }

        this.fileName = file.name;
    },

    dropFile(event) {
        this.dragging = false;
        const file = event.dataTransfer?.files?.[0];

        if (!file || !this.validarArchivo(file)) {
            return;
        }

        const input = this.$refs.designInput;

        if (!input) {
            return;
        }

        try {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            this.fileName = file.name;
        } catch (error) {
            this.mostrarError(
                'No fue posible cargar el archivo',
                'Haz clic en “Explorar archivos” y selecciona la imagen manualmente.'
            );
        }
    },

    validarArchivo(file) {
        const allowedTypes = ['image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            this.mostrarError(
                'Formato no permitido',
                'Selecciona una imagen JPG, JPEG o PNG.'
            );
            return false;
        }

        if (file.size > maxSize) {
            this.mostrarError(
                'Archivo demasiado grande',
                'La imagen no debe superar los 5 MB.'
            );
            return false;
        }

        return true;
    },

    mostrarError(title, text) {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                title,
                text,
                confirmButtonColor: '#006492',
            });
            return;
        }

        window.alert(`${title}\n\n${text}`);
    },

    confirmarEliminacion(id, nombre) {
        const ejecutarEliminacion = () => {
            this.$wire.call('eliminarImagenReconocimiento', id);
        };

        if (!window.Swal) {
            if (window.confirm(`¿Deseas eliminar el diseño “${nombre}”?`)) {
                ejecutarEliminacion();
            }
            return;
        }

        window.Swal.fire({
            title: '¿Eliminar este diseño?',
            text: `Se procesará “${nombre}”. Si está en uso, se desactivará para conservar el historial.`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#52525b',
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarEliminacion();
            }
        });
    },

    cardVisible(element) {
        if (!element?.dataset) {
            return true;
        }

        const searchValue = this.search.toLocaleLowerCase().trim();
        const cardSearch = (element.dataset.search ?? '').toLocaleLowerCase();
        const matchesSearch = cardSearch.includes(searchValue);
        const matchesStatus = this.status === 'todos'
            || element.dataset.status === this.status;

        return matchesSearch && matchesStatus;
    },

    limpiarDropzone() {
        this.fileName = '';
        this.uploadProgress = 0;
        this.uploading = false;

        if (this.$refs.designInput) {
            this.$refs.designInput.value = '';
        }
    },
});

const registerSweetAlertListener = () => {
    if (!window.Livewire || !window.Swal || window.__miniSystemsSwalListener) {
        return;
    }

    window.__miniSystemsSwalListener = true;

    window.Livewire.on('swal', (payload = {}) => {
        const data = Array.isArray(payload) ? (payload[0] ?? {}) : payload;

        const Toast = window.Swal.mixin({
            toast: true,
            position: data.position ?? 'top-end',
            showConfirmButton: false,
            timer: data.timer ?? 3200,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', window.Swal.stopTimer);
                toast.addEventListener('mouseleave', window.Swal.resumeTimer);
            },
        });

        Toast.fire({
            icon: data.icon ?? 'success',
            title: data.title ?? 'Operación realizada',
            text: data.text ?? undefined,
        });
    });
};

document.addEventListener('livewire:init', registerSweetAlertListener);
document.addEventListener('DOMContentLoaded', registerSweetAlertListener);
registerSweetAlertListener();

/**
 * Editor TinyMCE reutilizable para campos Livewire.
 *
 * El textarea original conserva wire:model. TinyMCE actualiza su valor y
 * dispara un evento input para que Livewire reciba el HTML sin perderlo al
 * enviar el formulario.
 */
window.miniSystemsTinyMce = ({
    editorId,
    height = 260,
    placeholder = '',
}) => ({
    editor: null,
    ready: false,

    init() {
        this.$nextTick(() => this.initializeEditor());
    },

    async initializeEditor() {
        if (!window.tinymce || !this.$refs.textarea) {
            this.showLoadError();
            return;
        }

        const previousEditor = window.tinymce.get(editorId);
        if (previousEditor) {
            previousEditor.remove();
        }

        const darkMode = document.documentElement.classList.contains('dark');

        try {
            const editors = await window.tinymce.init({
                target: this.$refs.textarea,
                license_key: 'gpl',
                height,
                min_height: 220,
                menubar: false,
                branding: false,
                promotion: false,
                resize: true,
                statusbar: true,
                elementpath: false,
                plugins: 'lists wordcount',
                toolbar: 'undo redo | bold italic underline | bullist numlist | outdent indent | removeformat',
                toolbar_mode: 'sliding',
                placeholder,
                skin: darkMode ? 'oxide-dark' : 'oxide',
                content_css: darkMode ? 'dark' : 'default',
                content_style: `
                    body {
                        font-family: Instrument Sans, Arial, sans-serif;
                        font-size: 14px;
                        line-height: 1.65;
                        padding: 8px 10px;
                    }
                    p { margin: 0 0 .75rem; }
                    ul, ol { margin: .5rem 0 .75rem 1.5rem; }
                `,
                setup: (editor) => {
                    this.editor = editor;

                    editor.on('init', () => {
                        this.ready = true;
                        const initialContent = this.$refs.textarea?.value ?? '';

                        if (initialContent && editor.getContent() !== initialContent) {
                            editor.setContent(initialContent);
                        }
                    });

                    editor.on('input change undo redo keyup', () => {
                        this.syncToLivewire();
                    });

                    editor.on('blur', () => {
                        this.syncToLivewire();
                    });
                },
            });

            this.editor = editors?.[0] ?? this.editor;
        } catch (error) {
            console.error('No fue posible inicializar TinyMCE.', error);
            this.showLoadError();
        }
    },

    syncToLivewire() {
        if (!this.editor || !this.ready || !this.$refs.textarea) {
            return;
        }

        const content = this.editor.getContent();

        if (this.$refs.textarea.value === content) {
            return;
        }

        this.$refs.textarea.value = content;
        this.$refs.textarea.dispatchEvent(new Event('input', { bubbles: true }));
    },

    setContent(html = '') {
        const content = typeof html === 'string' ? html : '';

        if (this.$refs.textarea) {
            this.$refs.textarea.value = content;
        }

        if (!this.editor || !this.ready) {
            return;
        }

        if (this.editor.getContent() !== content) {
            this.editor.setContent(content);
        }
    },

    showLoadError() {
        if (window.Swal) {
            window.Swal.fire({
                icon: 'warning',
                title: 'No se pudo cargar el editor',
                text: 'Puedes seguir escribiendo en el campo normal. Verifica la conexión a internet y vuelve a cargar la página.',
                confirmButtonColor: '#006492',
            });
        }
    },

    destroy() {
        this.syncToLivewire();

        if (this.editor) {
            this.editor.remove();
            this.editor = null;
        }
    },
});

/** Confirmaciones de acciones sensibles del módulo Etiquetas. */
window.etiquetasModule = () => ({
    exportandoExcel: false,
    iniciarExportacion() {
        this.exportandoExcel = true;
        window.setTimeout(() => {
            this.exportandoExcel = false;
        }, 3500);
    },
    confirmarEliminarAlumno(id, nombre) {
        this.confirmar({
            title: '¿Enviar a la papelera?',
            text: `El registro de “${nombre}” podrá restaurarse después.`,
            confirmButtonText: 'Sí, eliminar',
        }, () => this.$wire.call('eliminarAlumno', id));
    },
    confirmarEliminarPlantilla(id, nombre) {
        this.confirmar({
            title: '¿Eliminar esta plantilla?',
            text: `El fondo “${nombre}” dejará de estar disponible para nuevos PDF.`,
            confirmButtonText: 'Sí, eliminar',
        }, () => this.$wire.call('eliminarPlantilla', id));
    },
    confirmarMasivo(accion, cantidad) {
        this.confirmar({
            title: '¿Eliminar los seleccionados?',
            text: `${cantidad} registro(s) serán enviados a la papelera.`,
            confirmButtonText: 'Sí, continuar',
        }, () => this.$wire.call('accionMasiva', accion));
    },
    confirmarDefinitivo(id, nombre) {
        this.confirmar({
            title: 'Eliminación definitiva',
            text: `“${nombre}” no podrá recuperarse.`,
            confirmButtonText: 'Eliminar definitivamente',
        }, () => this.$wire.call('eliminarDefinitivamente', id));
    },
    confirmar(options, callback) {
        if (!window.Swal) {
            if (window.confirm(options.text)) callback();
            return;
        }
        window.Swal.fire({
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: true,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#52525b',
            ...options,
        }).then((result) => {
            if (result.isConfirmed) callback();
        });
    },
});
