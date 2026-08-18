<!-- Modal PDF Preview Universal -->
<div id="modal-pdf-preview" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm overflow-y-auto flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden flex flex-col" style="height: 90vh;">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                Vista Previa del PDF
            </h3>
            <button onclick="closePdfModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex-1 bg-gray-100 p-2">
            <iframe id="pdf-iframe" src="" class="w-full h-full rounded border border-gray-300"></iframe>
        </div>
        
        <div class="p-4 border-t flex justify-end gap-3 bg-gray-50">
            <button type="button" onclick="closePdfModal()" class="px-5 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">Cancelar</button>
            <button type="button" id="btn-descargar-pdf-universal" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descargar a Escritorio
            </button>
        </div>
    </div>
</div>

<script>
    let currentPdfUrl = null;

    function previewPdf(url) {
        currentPdfUrl = url;
        document.getElementById('pdf-iframe').src = url + '?preview=1';
        document.getElementById('modal-pdf-preview').classList.remove('hidden');
    }

    function closePdfModal() {
        document.getElementById('modal-pdf-preview').classList.add('hidden');
        document.getElementById('pdf-iframe').src = '';
        currentPdfUrl = null;
    }

    document.getElementById('btn-descargar-pdf-universal').addEventListener('click', function() {
        if(!currentPdfUrl) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Descargando...';
        btn.disabled = true;

        fetch(currentPdfUrl + '?save_to_desktop=1')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('¡Éxito! ' + data.message);
                } else {
                    alert('Ocurrió un error al guardar el archivo.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al intentar descargar.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                closePdfModal();
            });
    });
</script>
