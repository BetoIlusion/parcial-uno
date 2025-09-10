<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <style>
        .uml-toolbar {
            display: flex;
            gap: .5rem;
            padding: .5rem;
            border-bottom: 1px solid #ddd;
        }

        .uml-canvas {
            width: 100%;
            height: calc(100vh - 140px);
            background: #fafafa;
        }

        .uml-sidebar {
            padding: .5rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .uml-toolbar button,
        .uml-sidebar button {
            padding: .4rem .6rem;
            font-size: .9rem;
        }
    </style>

    <div class="uml-toolbar">
        <button id="btn-add-class">Añadir Clase</button>
        <button id="btn-add-interface">Añadir Interfaz</button>
        <button id="btn-link">Modo Enlace</button>
        <button id="btn-zoom-in">Zoom +</button>
        <button id="btn-zoom-out">Zoom -</button>
        <button id="btn-auto-layout">Auto layout</button>
        <button id="btn-clear">Limpiar</button>
    </div>

    <div id="paper" class="uml-canvas"></div>

    <div class="uml-sidebar">
        <button id="btn-export-json">Exportar JSON</button>
        <button id="btn-import-json">Importar JSON</button>
        <input id="json-file" type="file" accept="application/json" style="display:none" />
        <button id="btn-export-svg">Exportar SVG</button>
        <button id="btn-export-png">Exportar PNG</button>
        <button id="btn-save-server">Guardar en servidor</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.5.0/backbone-min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jointjs/dist/joint.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jointjs/dist/joint.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const graph = new joint.dia.Graph();
            const paper = new joint.dia.Paper({
                el: document.getElementById('paper'),
                model: graph,
                width: '100%',
                height: '100%',
                gridSize: 10,
                drawGrid: true,
                background: {
                    color: '#fafafa'
                }
            });

            let zoom = 1;
            let linkMode = false;
            let pendingLinkSource = null;

            function addClass(x = 50, y = 50) {
                const rect = new joint.shapes.standard.HeaderedRectangle();
                rect.position(x, y);
                rect.resize(160, 110);
                rect.header.attr({
                    label: {
                        text: 'NuevaClase'
                    }
                });
                rect.body.attr({
                    label: {
                        text: '+ atributo: Tipo\n+ metodo(): void'
                    }
                });
                rect.addTo(graph);
            }

            function addInterface(x = 260, y = 50) {
                const rect = new joint.shapes.standard.HeaderedRectangle();
                rect.position(x, y);
                rect.resize(180, 110);
                rect.header.attr({
                    label: {
                        text: '«interface»\nIExample'
                    }
                });
                rect.body.attr({
                    label: {
                        text: '+ metodo(): void'
                    }
                });
                rect.addTo(graph);
            }

            function createLink(source, target) {
                const link = new joint.shapes.standard.Link({
                    attrs: {
                        line: {
                            stroke: '#444',
                            strokeWidth: 2,
                            targetMarker: {
                                type: 'path',
                                d: 'M 10 -5 0 0 10 5 z'
                            }
                        }
                    }
                });
                link.source(source);
                link.target(target);
                link.addTo(graph);
            }

            paper.on('element:pointerdown', function(elementView) {
                if (!linkMode) return;
                if (!pendingLinkSource) {
                    pendingLinkSource = {
                        id: elementView.model.id
                    };
                    elementView.highlight();
                } else {
                    const target = {
                        id: elementView.model.id
                    };
                    if (target.id !== pendingLinkSource.id) {
                        createLink(pendingLinkSource, target);
                    }
                    paper.findViewByModel(pendingLinkSource.id)?.unhighlight();
                    pendingLinkSource = null;
                    linkMode = false;
                }
            });

            function autoLayout() {
                const elements = graph.getElements();
                const cols = Math.ceil(Math.sqrt(elements.length || 1));
                const gapX = 240,
                    gapY = 180;
                elements.forEach((el, i) => {
                    const row = Math.floor(i / cols);
                    const col = i % cols;
                    el.position(40 + col * gapX, 40 + row * gapY);
                });
            }

            function exportJSON() {
                const json = JSON.stringify(graph.toJSON(), null, 2);
                const blob = new Blob([json], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'diagrama.json';
                a.click();
                URL.revokeObjectURL(url);
            }

            function importJSON(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const data = JSON.parse(e.target.result);
                    graph.fromJSON(data);
                };
                reader.readAsText(file);
            }

            function exportSVG() {
                paper.toSVG(function(svg) {
                    const blob = new Blob([svg], {
                        type: 'image/svg+xml'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'diagrama.svg';
                    a.click();
                    URL.revokeObjectURL(url);
                });
            }

            function exportPNG() {
                paper.toSVG(function(svg) {
                    const img = new Image();
                    const svgBlob = new Blob([svg], {
                        type: 'image/svg+xml;charset=utf-8'
                    });
                    const url = URL.createObjectURL(svgBlob);
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);
                        canvas.toBlob(function(blob) {
                            const pngUrl = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = pngUrl;
                            a.download = 'diagrama.png';
                            a.click();
                            URL.revokeObjectURL(pngUrl);
                        });
                    };
                    img.src = url;
                });
            }

            async function saveToServer() {
                const res = await fetch("{{ route('diagramas.updateContenido', $diagrama->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        data: graph.toJSON()
                    })
                });
                const out = await res.json();
                alert(out.message || 'Guardado');
            }

            // Botones
            document.getElementById('btn-add-class').onclick = () => addClass();
            document.getElementById('btn-add-interface').onclick = () => addInterface();
            document.getElementById('btn-link').onclick = () => linkMode = !linkMode;
            document.getElementById('btn-zoom-in').onclick = () => paper.scale(++zoom, zoom);
            document.getElementById('btn-zoom-out').onclick = () => paper.scale(--zoom, zoom);
            document.getElementById('btn-auto-layout').onclick = autoLayout;
            document.getElementById('btn-clear').onclick = () => graph.clear();
            document.getElementById('btn-export-json').onclick = exportJSON;
            document.getElementById('btn-import-json').onclick = () => document.getElementById('json-file').click();
            document.getElementById('json-file').onchange = (e) => {
                if (e.target.files[0]) importJSON(e.target.files[0]);
            };
            document.getElementById('btn-export-svg').onclick = exportSVG;
            document.getElementById('btn-export-png').onclick = exportPNG;
            document.getElementById('btn-save-server').onclick = saveToServer;

            // Cargar diagrama inicial desde Laravel
            const initialData = @json($contenido);
            graph.fromJSON(initialData);
        });
    </script>

</x-app-layout>
