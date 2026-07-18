window.systemImagesUploader = (config) => ({
    batch: null,
    queue: [],
    dragActive: false,
    busy: false,
    loadingBatch: true,
    globalError: null,
    pollTimer: null,
    lastBatchStatus: null,
    retryItemUuid: null,
    syncedSettingsBatch: null,
    beforeUnloadHandler: null,

    init() {
        this.beforeUnloadHandler = (event) => {
            if (!this.busy) return;

            event.preventDefault();
            event.returnValue = '';
        };

        window.addEventListener('beforeunload', this.beforeUnloadHandler);
        this.restoreBatch();

        this.pollTimer = window.setInterval(() => {
            if (!document.hidden && this.batch?.uuid) {
                this.refreshBatch(false);
            }
        }, 1400);
    },

    destroy() {
        if (this.pollTimer) {
            window.clearInterval(this.pollTimer);
        }

        if (this.beforeUnloadHandler) {
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        }
    },

    async restoreBatch() {
        this.loadingBatch = true;
        this.globalError = null;

        const remembered = window.localStorage.getItem('minisystems-system-images-batch');

        try {
            if (remembered) {
                const response = await this.request(this.url(config.urls.show, {
                    batch: remembered,
                }));

                this.applyBatch(response.batch, false);
                return;
            }
        } catch (error) {
            if (![404, 410].includes(error.status)) {
                this.globalError = error.message;
            }

            window.localStorage.removeItem('minisystems-system-images-batch');
        } finally {
            this.loadingBatch = false;
        }

        try {
            const response = await this.request(config.urls.active);
            this.applyBatch(response.batch, false);
        } catch (error) {
            this.globalError = error.message;
        } finally {
            this.loadingBatch = false;
        }
    },

    async refreshBatch(showErrors = true) {
        if (!this.batch?.uuid) return;

        try {
            const response = await this.request(this.url(config.urls.show, {
                batch: this.batch.uuid,
            }));

            this.applyBatch(response.batch, true);
        } catch (error) {
            if ([404, 410].includes(error.status)) {
                this.clearLocalState();
                return;
            }

            if (showErrors) {
                this.globalError = error.message;
            }
        }
    },

    applyBatch(batch, notify = true) {
        const previousStatus = this.batch?.status ?? this.lastBatchStatus;
        this.batch = batch;

        if (!batch) {
            this.lastBatchStatus = null;
            window.localStorage.removeItem('minisystems-system-images-batch');
            return;
        }

        window.localStorage.setItem('minisystems-system-images-batch', batch.uuid);
        this.lastBatchStatus = batch.status;

        if (this.syncedSettingsBatch !== batch.uuid && batch.settings) {
            this.syncedSettingsBatch = batch.uuid;
            this.$wire.call('cargarConfiguracionLote', batch.settings);
        }

        if (
            notify
            && previousStatus
            && previousStatus !== batch.status
            && this.isTerminal(batch.status)
        ) {
            const message = batch.status === 'completed'
                ? `${batch.completed_files} imágenes fueron procesadas correctamente.`
                : `${batch.completed_files} completadas y ${batch.failed_files} con error.`;

            this.toast(
                batch.status === 'completed' ? 'success' : 'warning',
                'Lote finalizado',
                message,
            );
        }
    },

    currentSettings() {
        return {
            marco_id: this.$wire.marco === '' || this.$wire.marco === null ? null : Number(this.$wire.marco),
            orientation_mode: this.$wire.orientationMode,
            square_mode: this.$wire.squareMode,
            missing_frame_behavior: this.$wire.missingFrameBehavior,
            fit_mode: this.$wire.fitMode,
            format: this.$wire.format,
            quality: Number(this.$wire.quality),
            rename_pattern: this.$wire.renamePattern,
            organize_folders: Boolean(this.$wire.organizeFolders),
            preset_social_id: this.$wire.presetSocialId === '' || this.$wire.presetSocialId === null
                ? null
                : Number(this.$wire.presetSocialId),
            desktop_width: Number(this.$wire.desktopWidth),
            desktop_height: Number(this.$wire.desktopHeight),
            mobile_width: Number(this.$wire.mobileWidth),
            mobile_height: Number(this.$wire.mobileHeight),
        };
    },

    async selectFiles(fileList) {
        const wrappers = Array.from(fileList ?? []).map((file) => ({
            file,
            relativePath: file.webkitRelativePath || '',
        }));

        await this.prepareFiles(wrappers);
    },

    async dropFiles(event) {
        this.dragActive = false;
        const transfer = event.dataTransfer;

        if (!transfer) return;

        const entries = Array.from(transfer.items ?? [])
            .map((item) => item.webkitGetAsEntry?.())
            .filter(Boolean);

        if (entries.length === 0) {
            await this.selectFiles(transfer.files);
            return;
        }

        const wrappers = [];

        for (const entry of entries) {
            wrappers.push(...await this.readEntry(entry, ''));
        }

        await this.prepareFiles(wrappers);
    },

    async readEntry(entry, parentPath) {
        if (entry.isFile) {
            return await new Promise((resolve) => {
                entry.file(
                    (file) => resolve([{
                        file,
                        relativePath: `${parentPath}${file.name}`,
                    }]),
                    () => resolve([]),
                );
            });
        }

        if (!entry.isDirectory) return [];

        const path = `${parentPath}${entry.name}/`;
        const reader = entry.createReader();
        const children = [];

        while (true) {
            const page = await new Promise((resolve) => {
                reader.readEntries(resolve, () => resolve([]));
            });

            if (!page.length) break;
            children.push(...page);
        }

        const files = [];

        for (const child of children) {
            files.push(...await this.readEntry(child, path));
        }

        return files;
    },

    async prepareFiles(wrappers) {
        this.globalError = null;

        const images = wrappers.filter(({ file }) => this.isAllowedImage(file));
        const rejected = wrappers.length - images.length;

        if (images.length === 0) {
            this.alert(
                'No se encontraron imágenes compatibles',
                'Selecciona archivos JPG, JPEG, PNG o WebP de hasta '
                    + `${config.maxFileMb} MB cada uno.`,
            );
            return;
        }

        if (images.length > config.maxFiles) {
            this.alert(
                'El lote es demasiado grande',
                `Seleccionaste ${images.length} imágenes. El máximo es ${config.maxFiles}.`,
            );
            return;
        }

        const prepared = [];

        for (const wrapper of images) {
            prepared.push({
                ...wrapper,
                fingerprint: await this.fingerprint(wrapper),
                status: 'waiting',
                progress: 0,
                size: wrapper.file.size,
                attempts: 0,
                error: null,
                itemUuid: null,
            });
        }

        if (rejected > 0) {
            this.toast(
                'warning',
                `${rejected} archivo(s) omitido(s)`,
                'Solo se agregaron imágenes compatibles y dentro del límite de peso.',
            );
        }

        if (this.batch) {
            await this.resumePendingUploads(prepared);
            return;
        }

        await this.createBatch(prepared);
    },

    async createBatch(prepared) {
        this.busy = true;
        this.queue = prepared;

        const files = prepared.map((entry) => ({
            fingerprint: entry.fingerprint,
            name: entry.file.name,
            relative_path: entry.relativePath || null,
            size: entry.size,
            mime: entry.file.type || null,
            last_modified: entry.file.lastModified || 0,
        }));

        try {
            const response = await this.request(config.urls.store, {
                method: 'POST',
                body: JSON.stringify({
                    settings: this.currentSettings(),
                    files,
                }),
            });

            this.applyBatch(response.batch, false);
            this.attachServerItems();
            await this.uploadSequentially();
        } catch (error) {
            if (error.status === 409 && error.data?.batch) {
                this.applyBatch(error.data.batch, false);
                await this.resumePendingUploads(prepared);
                return;
            }

            this.globalError = error.message;
            this.alert('No fue posible crear el lote', error.message);
        } finally {
            this.busy = false;
        }
    },

    async resumePendingUploads(prepared) {
        const pending = (this.batch?.items ?? []).filter((item) =>
            ['pending_upload', 'upload_failed'].includes(item.status),
        );

        if (pending.length === 0) {
            this.alert(
                'Este lote ya está en proceso',
                this.isTerminal(this.batch?.status)
                    ? 'Pulsa “Nuevo lote” para seleccionar más imágenes.'
                    : 'Espera a que termine el procesamiento actual.',
            );
            return;
        }

        const matched = [];
        const unmatched = [];
        const usedItems = new Set();
        const requestedRetry = this.retryItemUuid
            ? pending.find((candidate) => candidate.uuid === this.retryItemUuid)
            : null;

        for (const entry of prepared) {
            let item = null;

            if (
                requestedRetry
                && !usedItems.has(requestedRetry.uuid)
                && requestedRetry.original_name === entry.file.name
                && Number(requestedRetry.original_size) === Number(entry.size)
            ) {
                item = requestedRetry;
            }

            if (!item) {
                item = pending.find((candidate) =>
                    !usedItems.has(candidate.uuid)
                    && candidate.fingerprint === entry.fingerprint,
                );
            }

            if (!item) {
                const candidates = pending.filter((candidate) =>
                    !usedItems.has(candidate.uuid)
                    && candidate.original_name === entry.file.name
                    && Number(candidate.original_size) === Number(entry.size),
                );

                if (candidates.length === 1) {
                    item = candidates[0];
                }
            }

            if (item) {
                usedItems.add(item.uuid);
                matched.push({
                    ...entry,
                    itemUuid: item.uuid,
                });
            } else {
                unmatched.push(entry.file.name);
            }
        }

        this.retryItemUuid = null;

        if (matched.length === 0) {
            this.alert(
                'Las imágenes no coinciden con el lote pendiente',
                'Vuelve a seleccionar la misma carpeta o los mismos archivos que faltan por subir.',
            );
            return;
        }

        this.queue = matched;
        this.busy = true;

        if (unmatched.length > 0) {
            this.toast(
                'info',
                `${unmatched.length} archivo(s) no pertenecen al lote`,
                'Se subirán únicamente las fotografías pendientes que coincidan.',
            );
        }

        try {
            await this.uploadSequentially();
        } finally {
            this.busy = false;
        }
    },

    attachServerItems() {
        const items = this.batch?.items ?? [];

        this.queue.forEach((entry, index) => {
            entry.itemUuid = items[index]?.uuid ?? null;
        });
    },

    async uploadSequentially() {
        const pending = this.queue.filter((entry) => entry.itemUuid && entry.status !== 'uploaded');
        let cursor = 0;
        const workers = Array.from({ length: Math.min(Number(config.uploadConcurrency || 1), pending.length || 1) }, async () => {
            while (cursor < pending.length) {
                const entry = pending[cursor++];
                await this.uploadWithRetries(entry);
            }
        });

        await Promise.all(workers);
        await this.refreshBatch(false);
    },

    async uploadWithRetries(entry) {
        const maxAttempts = 3;
        let lastError = null;

        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            entry.attempts = attempt;
            entry.status = 'uploading';
            entry.error = null;

            try {
                const response = await this.uploadRequest(entry, (progress) => {
                    entry.progress = progress;
                });

                entry.progress = 100;
                entry.status = 'uploaded';
                entry.file = null;
                this.applyBatch(response.batch, false);
                return;
            } catch (error) {
                if (error.status === 409) {
                    await this.refreshBatch(false);
                    const serverItem = (this.batch?.items ?? []).find((item) => item.uuid === entry.itemUuid);

                    if (serverItem && !['pending_upload', 'upload_failed'].includes(serverItem.status)) {
                        entry.progress = 100;
                        entry.status = 'uploaded';
                        entry.file = null;
                        entry.error = null;
                        return;
                    }
                }

                lastError = error;
                entry.status = 'retrying';
                entry.error = error.message;

                if (attempt < maxAttempts) {
                    await this.sleep(700 * attempt);
                }
            }
        }

        entry.status = 'upload_error';
        entry.error = lastError?.message ?? 'La subida no pudo completarse.';

        try {
            const response = await this.request(this.url(config.urls.uploadFailed, {
                batch: this.batch.uuid,
                item: entry.itemUuid,
            }), {
                method: 'POST',
                body: JSON.stringify({ error: entry.error }),
            });

            this.applyBatch(response.batch, false);
        } catch (_) {
            // El estado local conserva el error aunque la red siga sin responder.
        }
    },

    uploadRequest(entry, onProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const url = this.url(config.urls.upload, {
                batch: this.batch.uuid,
                item: entry.itemUuid,
            });

            xhr.open('POST', url, true);
            xhr.responseType = 'json';
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', config.csrf);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) return;
                onProgress(Math.min(99, Math.round((event.loaded / event.total) * 100)));
            });

            xhr.addEventListener('load', () => {
                const data = xhr.response ?? this.safeJson(xhr.responseText);

                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(data);
                    return;
                }

                reject(this.httpError(xhr.status, data));
            });

            xhr.addEventListener('error', () => {
                reject(this.httpError(0, {
                    message: 'La conexión se interrumpió durante la subida.',
                }));
            });

            xhr.addEventListener('timeout', () => {
                reject(this.httpError(0, {
                    message: 'La subida tardó demasiado y fue cancelada.',
                }));
            });

            xhr.timeout = 10 * 60 * 1000;

            const form = new FormData();
            form.append('file', entry.file, entry.file.name);
            xhr.send(form);
        });
    },

    async retryLocalUpload(item) {
        const entry = this.localEntry(item);

        if (!entry?.file) {
            this.chooseRetryUpload(item);
            return;
        }

        this.busy = true;
        entry.status = 'waiting';
        entry.progress = 0;
        entry.error = null;

        try {
            await this.uploadWithRetries(entry);
            await this.refreshBatch(false);
        } finally {
            this.busy = false;
        }
    },

    async retryProcessing(item) {
        try {
            const response = await this.request(this.url(config.urls.retry, {
                batch: this.batch.uuid,
                item: item.uuid,
            }), {
                method: 'POST',
                body: JSON.stringify({}),
            });

            this.applyBatch(response.batch, false);
            this.toast('success', 'Reintento enviado', 'La imagen volvió a la cola de procesamiento.');
        } catch (error) {
            this.alert('No fue posible reintentar', error.message);
        }
    },

    chooseRetryUpload(item) {
        this.retryItemUuid = item.uuid;
        this.$refs.fileInput?.click();
    },

    async newBatch() {
        if (this.busy) {
            await this.alert(
                'La subida sigue en curso',
                'Espera a que termine el archivo actual antes de cancelar o crear otro lote.',
            );
            return;
        }

        if (!this.batch?.uuid) {
            this.clearLocalState();
            return;
        }

        const confirmed = await this.confirm(
            '¿Crear un lote nuevo?',
            this.isTerminal(this.batch.status)
                ? 'Se eliminarán las copias temporales del lote actual.'
                : 'Se cancelará el lote actual y se eliminarán los archivos ya subidos.',
        );

        if (!confirmed) return;

        try {
            await this.request(this.url(config.urls.destroy, {
                batch: this.batch.uuid,
            }), {
                method: 'DELETE',
            });

            this.clearLocalState();
            this.toast('success', 'Lote preparado', `Ya puedes seleccionar hasta ${config.maxFiles} imágenes nuevas.`);
        } catch (error) {
            this.alert('No fue posible eliminar el lote', error.message);
        }
    },

    clearLocalState() {
        this.batch = null;
        this.queue = [];
        this.busy = false;
        this.globalError = null;
        this.lastBatchStatus = null;
        this.retryItemUuid = null;
        this.syncedSettingsBatch = null;
        window.localStorage.removeItem('minisystems-system-images-batch');

        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        if (this.$refs.folderInput) this.$refs.folderInput.value = '';
    },

    localEntry(item) {
        return this.queue.find((entry) => entry.itemUuid === item.uuid) ?? null;
    },

    visibleStatus(item) {
        const local = this.localEntry(item);

        if (local && ['waiting', 'uploading', 'retrying', 'upload_error'].includes(local.status)) {
            return local.status;
        }

        return item.status;
    },

    visibleProgress(item) {
        const local = this.localEntry(item);

        if (local) return local.progress;
        if (item.status === 'pending_upload' || item.status === 'upload_failed') return 0;
        return 100;
    },

    statusText(status) {
        return {
            pending_upload: 'Pendiente de subir',
            waiting: 'En espera',
            uploading: 'Subiendo',
            retrying: 'Reintentando subida',
            upload_error: 'Error de subida',
            upload_failed: 'Error de subida',
            queued: 'En cola',
            processing: 'Procesando',
            completed: 'Completada',
            failed: 'Error al procesar',
        }[status] ?? status;
    },

    statusClasses(status) {
        if (status === 'completed') {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300';
        }

        if (['failed', 'upload_failed', 'upload_error'].includes(status)) {
            return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300';
        }

        if (['processing', 'queued'].includes(status)) {
            return 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900/50 dark:bg-violet-950/30 dark:text-violet-300';
        }

        return 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-300';
    },

    batchStatusText(status) {
        return {
            uploading: 'Subiendo fotografías',
            processing: 'Procesando el lote',
            completed: 'Lote completado',
            partial: 'Lote completado con errores',
            failed: 'El lote contiene errores',
        }[status] ?? 'Preparando lote';
    },

    get uploadProgress() {
        const total = this.queue.reduce((sum, entry) => sum + Number(entry.size || 0), 0);

        if (total <= 0) return 0;

        const uploaded = this.queue.reduce((sum, entry) => {
            return sum + (Number(entry.size || 0) * (entry.progress / 100));
        }, 0);

        return Math.min(100, Math.round((uploaded / total) * 100));
    },

    get processingProgress() {
        if (!this.batch?.total_files) return 0;
        return Math.min(100, Math.round((this.batch.processed_files / this.batch.total_files) * 100));
    },

    get pendingUploadCount() {
        return (this.batch?.items ?? []).filter((item) =>
            ['pending_upload', 'upload_failed'].includes(item.status),
        ).length;
    },

    isAllowedImage(file) {
        const allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        const extension = file.name.split('.').pop()?.toLowerCase();
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        return file.size > 0
            && file.size <= config.maxFileBytes
            && (allowedMimes.includes(file.type) || allowedExtensions.includes(extension));
    },

    async fingerprint(wrapper) {
        const file = wrapper.file;
        const relative = wrapper.relativePath || file.webkitRelativePath || file.name;
        const raw = `${relative}\u001f${file.name}\u001f${file.size}\u001f${file.lastModified || 0}`;

        if (window.crypto?.subtle && window.TextEncoder) {
            const bytes = new TextEncoder().encode(raw);
            const digest = await window.crypto.subtle.digest('SHA-256', bytes);

            return Array.from(new Uint8Array(digest))
                .map((byte) => byte.toString(16).padStart(2, '0'))
                .join('');
        }

        return raw.slice(0, 1000);
    },

    async request(url, options = {}) {
        const method = options.method ?? 'GET';
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        };

        if (method !== 'GET') {
            headers['X-CSRF-TOKEN'] = config.csrf;
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            method,
            headers,
        });

        const data = await response.json().catch(() => ({
            message: response.statusText || 'Ocurrió un error inesperado.',
        }));

        if (!response.ok) {
            throw this.httpError(response.status, data);
        }

        return data;
    },

    httpError(status, data) {
        const validation = data?.errors
            ? Object.values(data.errors).flat().join(' ')
            : null;
        const error = new Error(validation || data?.message || 'No fue posible completar la operación.');
        error.status = status;
        error.data = data;

        return error;
    },

    safeJson(value) {
        try {
            return JSON.parse(value || '{}');
        } catch (_) {
            return { message: 'El servidor devolvió una respuesta no válida.' };
        }
    },

    url(template, replacements) {
        return Object.entries(replacements).reduce((url, [key, value]) => {
            return url.replace(`__${key.toUpperCase()}__`, encodeURIComponent(value));
        }, template);
    },

    isTerminal(status) {
        return ['completed', 'partial', 'failed'].includes(status);
    },

    formatBytes(bytes) {
        const value = Number(bytes ?? 0);

        if (value <= 0) return '0 KB';

        const units = ['B', 'KB', 'MB', 'GB'];
        const power = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
        const amount = value / (1024 ** power);

        return `${amount.toLocaleString('es-MX', {
            minimumFractionDigits: power === 0 ? 0 : 2,
            maximumFractionDigits: power === 0 ? 0 : 2,
        })} ${units[power]}`;
    },

    formatDate(value) {
        if (!value) return '';

        return new Intl.DateTimeFormat('es-MX', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));
    },

    sleep(milliseconds) {
        return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
    },

    alert(title, text) {
        if (window.Swal) {
            return window.Swal.fire({
                icon: 'warning',
                title,
                text,
                confirmButtonColor: '#059669',
            });
        }

        window.alert(`${title}\n\n${text}`);
        return Promise.resolve();
    },

    toast(icon, title, text = null) {
        if (!window.Swal) return;

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            text,
            showConfirmButton: false,
            timer: 3600,
            timerProgressBar: true,
        });
    },

    async confirm(title, text) {
        if (!window.Swal) {
            return window.confirm(`${title}\n\n${text}`);
        }

        const result = await window.Swal.fire({
            icon: 'warning',
            title,
            text,
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
        });

        return result.isConfirmed;
    },
});
