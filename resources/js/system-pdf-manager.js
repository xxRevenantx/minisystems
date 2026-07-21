window.systemPdfManager = (config) => ({
    maxFiles: Number(config.maxFiles),
    operation: 'compress',
    files: [],
    batch: null,
    history: [],
    permissions: [],
    selectedZipEntries: [],
    dragActive: false,
    busy: false,
    loading: true,
    globalError: null,
    pollTimer: null,
    draggedFileIndex: null,
    draggedPageIndex: null,
    pagePlan: [],
    selectedPages: [],
    compressionProfile: 'auto',
    customQuality: 75,
    outputName: 'documentos_combinados',
    splitMode: 'each',
    splitRanges: '1-3; 4-6',
    splitEvery: 1,
    securityMode: 'protect',
    newPassword: '',
    ownerPassword: '',
    allowPrint: 'full',
    allowModify: 'none',
    allowExtract: false,

    init() {
        this.restore();
        this.pollTimer = window.setInterval(() => {
            if (!document.hidden && this.batch?.uuid && !this.isTerminal(this.batch.status)) {
                this.refreshBatch(false);
            }
        }, 1500);
    },

    destroy() {
        if (this.pollTimer) window.clearInterval(this.pollTimer);
    },

    async restore() {
        this.loading = true;
        const remembered = window.localStorage.getItem('minisystems-system-pdf-batch');
        try {
            if (remembered) {
                const response = await this.request(this.url(config.urls.show, { batch: remembered }));
                this.applyBatch(response.batch);
            } else {
                const response = await this.request(config.urls.active);
                if (response.batch) this.applyBatch(response.batch);
            }
        } catch (error) {
            if (![404, 410].includes(error.status)) this.globalError = error.message;
            window.localStorage.removeItem('minisystems-system-pdf-batch');
        } finally {
            await this.loadHistory();
            if (config.canAdmin) await this.loadPermissions();
            this.loading = false;
        }
    },

    chooseOperation(value) {
        if (this.batch && !this.isTerminal(this.batch.status)) {
            this.alert('Lote activo', 'Finaliza o elimina el lote actual antes de cambiar de herramienta.');
            return;
        }
        this.operation = value;
        this.files = [];
        this.batch = null;
        this.pagePlan = [];
        this.selectedPages = [];
        this.globalError = null;
        window.localStorage.removeItem('minisystems-system-pdf-batch');
    },

    selectFiles(fileList) {
        this.addFiles(Array.from(fileList ?? []));
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    dropFiles(event) {
        this.dragActive = false;
        this.addFiles(Array.from(event.dataTransfer?.files ?? []));
    },

    addFiles(incoming) {
        if (this.batch) {
            this.alert('Configuración bloqueada', 'Crea un lote nuevo para seleccionar otros archivos.');
            return;
        }

        const accepted = [];
        const rejected = [];
        for (const file of incoming) {
            const extension = (file.name.split('.').pop() || '').toLowerCase();
            const allowed = this.operation === 'combine'
                ? ['pdf', 'jpg', 'jpeg', 'png', 'webp']
                : ['pdf'];
            if (!allowed.includes(extension) || file.size <= 0 || file.size > Number(config.maxFileMb) * 1024 * 1024) {
                rejected.push(file.name);
                continue;
            }
            accepted.push({
                id: `${file.name}-${file.size}-${file.lastModified}-${crypto.randomUUID?.() || Math.random()}`,
                file,
                name: file.name,
                size: file.size,
                type: file.type,
                fingerprint: `${file.name}|${file.size}|${file.lastModified}`,
                status: 'local',
                error: null,
            });
        }

        const maxAllowed = Number(config.maxFiles);
        const available = Math.max(0, maxAllowed - this.files.length);
        this.files.push(...accepted.slice(0, available));

        if (accepted.length > available || rejected.length) {
            const details = [];
            if (accepted.length > available) details.push(`Solo se admiten ${maxAllowed} archivos por lote.`);
            if (rejected.length) details.push(`${rejected.length} archivo(s) no son compatibles o superan ${config.maxFileMb} MB.`);
            this.alert('Algunos archivos no se agregaron', details.join(' '));
        }
    },

    removeLocal(index) {
        if (this.batch) return;
        this.files.splice(index, 1);
    },

    dragFile(index) {
        this.draggedFileIndex = index;
    },

    dropFile(index) {
        if (this.draggedFileIndex === null || this.draggedFileIndex === index) return;
        const list = this.batch ? this.batch.items : this.files;
        const [moved] = list.splice(this.draggedFileIndex, 1);
        list.splice(index, 0, moved);
        this.draggedFileIndex = null;
    },

    async createAndUpload() {
        if (!this.files.length || this.busy) return;
        if ((this.operation === 'reorder' || this.operation === 'security') && this.files.length !== 1) {
            this.alert('Selecciona un solo PDF', 'Ordenar y proteger/desbloquear trabajan con un documento por operación.');
            return;
        }

        this.busy = true;
        this.globalError = null;
        try {
            const response = await this.request(config.urls.store, {
                method: 'POST',
                body: JSON.stringify({
                    operation: this.operation,
                    files: this.files.map((entry) => ({
                        name: entry.name,
                        size: entry.size,
                        type: entry.type,
                        fingerprint: entry.fingerprint,
                    })),
                }),
            });
            this.applyBatch(response.batch);
            await this.uploadQueue();
        } catch (error) {
            this.globalError = error.message;
            this.alert('No se pudo crear el lote', error.message);
        } finally {
            this.busy = false;
        }
    },

    async uploadQueue() {
        const serverItems = [...(this.batch?.items ?? [])].sort((a, b) => a.position - b.position);
        const pairs = this.files.map((entry, index) => ({ entry, item: serverItems[index] })).filter((pair) => pair.item);
        let cursor = 0;
        const workers = Array.from({ length: Math.min(Number(config.uploadConcurrency), pairs.length) }, async () => {
            while (cursor < pairs.length) {
                const pair = pairs[cursor++];
                await this.uploadOne(pair.entry, pair.item);
            }
        });
        await Promise.all(workers);
        await this.refreshBatch();
    },

    async uploadOne(entry, item) {
        entry.status = 'uploading';
        const form = new FormData();
        form.append('file', entry.file, entry.name);
        try {
            const response = await this.request(this.url(config.urls.upload, {
                batch: this.batch.uuid,
                item: item.uuid,
            }), { method: 'POST', body: form, isForm: true });
            entry.status = 'uploaded';
            this.applyBatch(response.batch);
        } catch (error) {
            entry.status = 'failed';
            entry.error = error.message;
        }
    },

    async refreshBatch(showError = true) {
        if (!this.batch?.uuid) return;
        try {
            const response = await this.request(this.url(config.urls.show, { batch: this.batch.uuid }));
            const previous = this.batch?.status;
            this.applyBatch(response.batch);
            if (previous && previous !== response.batch.status && this.isTerminal(response.batch.status)) {
                this.toast(response.batch.status === 'completed' ? 'success' : 'warning', 'Proceso finalizado', this.summaryText(response.batch));
                await this.loadHistory();
            }
        } catch (error) {
            if (showError) this.globalError = error.message;
        }
    },

    applyBatch(batch) {
        const previousUuid = this.batch?.uuid;
        this.batch = batch;
        if (!batch) return;
        if (previousUuid && previousUuid !== batch.uuid) {
            this.pagePlan = [];
            this.selectedPages = [];
            this.selectedZipEntries = [];
        }
        this.operation = batch.operation;
        window.localStorage.setItem('minisystems-system-pdf-batch', batch.uuid);
        this.syncPageEditors();
        if (this.isTerminal(batch.status) && this.selectedZipEntries.length === 0) {
            this.selectedZipEntries = (batch.zip_entries ?? []).map((entry) => entry.key);
        }
    },

    syncPageEditors() {
        const item = this.batch?.items?.[0];
        if (!item?.pages?.length) return;

        if (this.operation === 'reorder' && this.pagePlan.length === 0) {
            this.pagePlan = item.pages.map((page) => ({
                key: `${page.number}-${crypto.randomUUID?.() || Math.random()}`,
                source: page.number,
                rotation: 0,
                thumbnail_url: page.thumbnail_url,
            }));
        }

        if (this.operation === 'split' && this.selectedPages.length === 0) {
            this.selectedPages = item.pages.map((page) => page.number);
        }
    },

    async setPassword(item) {
        let password = '';
        if (window.Swal) {
            const result = await window.Swal.fire({
                title: 'Contraseña del PDF',
                text: 'La contraseña se almacena cifrada y se elimina cuando vence el lote.',
                input: 'password',
                inputAttributes: { autocomplete: 'current-password' },
                showCancelButton: true,
                confirmButtonText: 'Volver a analizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#006492',
                inputValidator: (value) => value ? undefined : 'Escribe la contraseña.',
            });
            if (!result.isConfirmed) return;
            password = result.value;
        } else {
            password = window.prompt('Escribe la contraseña del PDF:') || '';
            if (!password) return;
        }

        this.busy = true;
        try {
            const response = await this.request(this.url(config.urls.password, {
                batch: this.batch.uuid,
                item: item.uuid,
            }), { method: 'POST', body: JSON.stringify({ password }) });
            this.applyBatch(response.batch);
            this.toast('success', 'Contraseña guardada', response.message);
        } catch (error) {
            this.alert('Contraseña no aceptada', error.message);
        } finally {
            this.busy = false;
        }
    },

    async startProcessing() {
        if (!this.batch || this.batch.status !== 'ready' || this.busy) return;
        const payload = { item_order: this.batch.items.map((item) => item.uuid) };

        if (this.operation === 'compress') {
            payload.compression_profile = this.compressionProfile;
            payload.custom_quality = Number(this.customQuality);
        }
        if (this.operation === 'combine') payload.output_name = this.outputName;
        if (this.operation === 'split') {
            payload.split_mode = this.splitMode;
            payload.split_ranges = this.splitRanges;
            payload.split_every = Number(this.splitEvery);
            payload.selected_pages = this.selectedPages;
        }
        if (this.operation === 'reorder') {
            payload.page_plan = this.pagePlan.map((page) => ({ source: page.source, rotation: page.rotation }));
        }
        if (this.operation === 'security') {
            payload.security_mode = this.securityMode;
            payload.new_password = this.newPassword;
            payload.owner_password = this.ownerPassword;
            payload.allow_print = this.allowPrint;
            payload.allow_modify = this.allowModify;
            payload.allow_extract = this.allowExtract;
        }

        this.busy = true;
        try {
            const response = await this.request(this.url(config.urls.start, { batch: this.batch.uuid }), {
                method: 'POST', body: JSON.stringify(payload),
            });
            this.applyBatch(response.batch);
            this.toast('success', 'Enviado a la cola', response.message);
        } catch (error) {
            this.alert('No se pudo iniciar', error.message);
        } finally {
            this.busy = false;
        }
    },

    dragPage(index) {
        this.draggedPageIndex = index;
    },

    dropPage(index) {
        if (this.draggedPageIndex === null || this.draggedPageIndex === index) return;
        const [moved] = this.pagePlan.splice(this.draggedPageIndex, 1);
        this.pagePlan.splice(index, 0, moved);
        this.draggedPageIndex = null;
    },

    rotatePage(index) {
        this.pagePlan[index].rotation = (Number(this.pagePlan[index].rotation) + 90) % 360;
    },

    duplicatePage(index) {
        const page = this.pagePlan[index];
        this.pagePlan.splice(index + 1, 0, {
            ...page,
            key: `${page.source}-${crypto.randomUUID?.() || Math.random()}`,
        });
    },

    deletePage(index) {
        if (this.pagePlan.length <= 1) {
            this.alert('Debe quedar una página', 'No puedes eliminar todas las páginas del documento.');
            return;
        }
        this.pagePlan.splice(index, 1);
    },

    toggleSelectedPage(page) {
        const index = this.selectedPages.indexOf(page);
        if (index >= 0) this.selectedPages.splice(index, 1);
        else this.selectedPages.push(page);
        this.selectedPages.sort((a, b) => a - b);
    },

    selectAllPages() {
        const pages = this.batch?.items?.[0]?.pages ?? [];
        this.selectedPages = pages.map((page) => page.number);
    },

    clearSelectedPages() {
        this.selectedPages = [];
    },

    async retryItem(item) {
        this.busy = true;
        try {
            const response = await this.request(this.url(config.urls.retry, {
                batch: this.batch.uuid,
                item: item.uuid,
            }), { method: 'POST', body: JSON.stringify({}) });
            this.applyBatch(response.batch);
        } catch (error) {
            this.alert('No se pudo reintentar', error.message);
        } finally {
            this.busy = false;
        }
    },

    async deleteCurrent() {
        if (!this.batch?.uuid) return;
        const confirmed = await this.confirm('¿Eliminar este lote?', 'Se borrarán originales, miniaturas y resultados temporales.');
        if (!confirmed) return;
        this.busy = true;
        try {
            await this.request(this.url(config.urls.destroy, { batch: this.batch.uuid }), { method: 'DELETE' });
            this.resetWorkspace();
            await this.loadHistory();
        } catch (error) {
            this.alert('No se pudo eliminar', error.message);
        } finally {
            this.busy = false;
        }
    },

    newWorkspace() {
        this.batch = null;
        this.files = [];
        this.pagePlan = [];
        this.selectedPages = [];
        this.globalError = null;
        this.newPassword = '';
        this.ownerPassword = '';
        this.selectedZipEntries = [];
        window.localStorage.removeItem('minisystems-system-pdf-batch');
    },

    resetWorkspace() {
        this.newWorkspace();
    },

    toggleZipEntry(key) {
        const index = this.selectedZipEntries.indexOf(key);
        if (index >= 0) this.selectedZipEntries.splice(index, 1);
        else this.selectedZipEntries.push(key);
    },

    selectedZipParts() {
        if (!this.batch?.uuid || this.selectedZipEntries.length === 0) return [];

        const allEntries = this.batch.zip_entries ?? [];
        if (this.selectedZipEntries.length === allEntries.length
            && allEntries.every((entry) => this.selectedZipEntries.includes(entry.key))) {
            const parts = this.batch.download_zip_parts ?? [];
            return parts.map((part) => ({ ...part, total: parts.length }));
        }

        const selected = allEntries.filter((entry) => this.selectedZipEntries.includes(entry.key));
        const maxFiles = Math.max(1, Number(config.zipPartMaxFiles || 1000));
        const maxBytes = Math.max(1, Number(config.zipPartMaxMb || 1000) * 1024 * 1024);
        const groups = [];
        let current = [];
        let bytes = 0;

        for (const entry of selected) {
            const size = Math.max(0, Number(entry.size || 0));
            if (current.length && (current.length >= maxFiles || bytes + size > maxBytes)) {
                groups.push(current);
                current = [];
                bytes = 0;
            }
            current.push(entry);
            bytes += size;
        }
        if (current.length) groups.push(current);

        const base = this.url(config.urls.downloadZip, { batch: this.batch.uuid });
        const keys = encodeURIComponent(selected.map((entry) => entry.key).join(','));
        return groups.map((group, index) => ({
            number: index + 1,
            total: groups.length,
            file_count: group.length,
            url: `${base}?results=${keys}&part=${index + 1}`,
        }));
    },

    async loadPermissions() {
        if (!config.canAdmin || !config.urls.permissions) return;
        try {
            const response = await this.request(config.urls.permissions);
            this.permissions = response.users ?? [];
        } catch (_) {
            this.permissions = [];
        }
    },

    async updatePermission(user) {
        if (!config.canAdmin || user.is_primary_admin) return;
        user.saving = true;
        try {
            const response = await this.request(this.url(config.urls.permissionUpdate, { user: user.id }), {
                method: 'PATCH',
                body: JSON.stringify(user.permissions),
            });
            user.permissions = response.permissions;
            this.toast('success', 'Permisos actualizados', user.name);
        } catch (error) {
            this.alert('No se pudieron guardar los permisos', error.message);
            await this.loadPermissions();
        } finally {
            user.saving = false;
        }
    },

    async loadHistory() {
        try {
            const response = await this.request(config.urls.history);
            this.history = response.batches ?? [];
        } catch (error) {
            // El historial no impide usar el módulo.
        }
    },

    async openHistory(batch) {
        try {
            const response = await this.request(this.url(config.urls.show, { batch: batch.uuid }));
            this.applyBatch(response.batch);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            this.alert('No se pudo abrir el lote', error.message);
        }
    },

    canStart() {
        if (this.batch?.status !== 'ready') return false;
        if (this.operation === 'reorder' && this.pagePlan.length === 0) return false;
        if (this.operation === 'split' && this.splitMode === 'selected' && this.selectedPages.length === 0) return false;
        if (this.operation === 'security' && this.securityMode === 'protect' && !this.newPassword) return false;
        return true;
    },

    operationTitle() {
        return {
            compress: 'Reducir peso', combine: 'Combinar PDF', split: 'Descombinar PDF',
            reorder: 'Ordenar PDF', security: 'Seguridad PDF',
        }[this.operation] || 'System PDF';
    },

    operationHint() {
        return {
            compress: 'Comprime hasta 100 PDF y conserva el original cuando no se obtiene ahorro.',
            combine: 'Une PDF e imágenes en el orden que definas mediante arrastrar y soltar.',
            split: 'Extrae páginas individuales, rangos, bloques o una selección visual.',
            reorder: 'Mueve, gira, duplica o elimina páginas antes de exportar.',
            security: 'Agrega cifrado AES-256 o elimina la contraseña con la clave válida.',
        }[this.operation] || '';
    },

    acceptedText() {
        return this.operation === 'combine' ? 'PDF, JPG, PNG o WebP' : 'Solo PDF';
    },

    progressPercent() {
        if (!this.batch?.total_files) return 0;
        if (['uploading', 'preparing', 'needs_attention', 'ready'].includes(this.batch.status)) {
            return Math.round((this.batch.uploaded_files / this.batch.total_files) * 100);
        }
        return Math.round((this.batch.processed_files / this.batch.total_files) * 100);
    },

    summaryText(batch) {
        if (batch.operation === 'combine') return batch.status === 'completed' ? 'El PDF combinado está listo.' : (batch.error || 'La combinación no pudo finalizar.');
        return `${batch.completed_files} completado(s) y ${batch.failed_files} con error.`;
    },

    statusLabel(status) {
        return {
            uploading: 'Subiendo', preparing: 'Preparando miniaturas', needs_attention: 'Requiere atención',
            ready: 'Listo para procesar', queued: 'En cola', processing: 'Procesando', completed: 'Completado',
            partial: 'Completado con errores', failed: 'Fallido', pending_upload: 'Pendiente', upload_failed: 'Error de carga',
            inspecting: 'Analizando', inspection_failed: 'Contraseña o error', queued_item: 'En cola',
        }[status] || status;
    },

    statusClass(status) {
        if (['completed', 'ready'].includes(status)) return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
        if (['failed', 'inspection_failed', 'upload_failed'].includes(status)) return 'bg-red-50 text-red-700 ring-red-200';
        if (['partial', 'needs_attention'].includes(status)) return 'bg-amber-50 text-amber-700 ring-amber-200';
        return 'bg-sky-50 text-sky-700 ring-sky-200';
    },

    isTerminal(status) {
        return ['completed', 'partial', 'failed'].includes(status);
    },

    formatBytes(bytes) {
        const value = Number(bytes || 0);
        if (value < 1024) return `${value} B`;
        const units = ['KB', 'MB', 'GB'];
        let size = value / 1024;
        let unit = units[0];
        for (let index = 1; index < units.length && size >= 1024; index++) {
            size /= 1024;
            unit = units[index];
        }
        return `${size.toLocaleString('es-MX', { maximumFractionDigits: 2 })} ${unit}`;
    },

    formatDate(value) {
        if (!value) return '—';
        return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
    },

    url(template, values) {
        let result = template;
        Object.entries(values).forEach(([key, value]) => {
            result = result.replace(`{${key}}`, encodeURIComponent(value));
        });
        return result;
    },

    async request(url, options = {}) {
        const headers = { Accept: 'application/json' };
        if (!options.isForm) headers['Content-Type'] = 'application/json';
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) headers['X-CSRF-TOKEN'] = token;
        const response = await fetch(url, { credentials: 'same-origin', ...options, headers: { ...headers, ...(options.headers || {}) } });
        let payload = {};
        try { payload = await response.json(); } catch (_) { payload = {}; }
        if (!response.ok) {
            const validation = payload.errors ? Object.values(payload.errors).flat().join(' ') : null;
            const error = new Error(validation || payload.message || `Error HTTP ${response.status}`);
            error.status = response.status;
            throw error;
        }
        return payload;
    },

    alert(title, text) {
        if (window.Swal) {
            window.Swal.fire({ icon: 'warning', title, text, confirmButtonColor: '#006492' });
        } else window.alert(`${title}\n\n${text}`);
    },

    toast(icon, title, text) {
        if (window.Swal) {
            window.Swal.fire({ toast: true, position: 'top-end', icon, title, text, timer: 3500, showConfirmButton: false });
        }
    },

    async confirm(title, text) {
        if (!window.Swal) return window.confirm(`${title}\n\n${text}`);
        const result = await window.Swal.fire({
            icon: 'warning', title, text, showCancelButton: true, confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#dc2626', reverseButtons: true,
        });
        return result.isConfirmed;
    },
});
