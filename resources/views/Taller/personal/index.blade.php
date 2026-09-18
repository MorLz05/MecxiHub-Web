@extends('taller.layouts.app')

@section('title', 'Personal del Taller - MecxiHub')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                <span
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-blue to-brand-darkblue text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-users text-lg"></i>
                </span>
                Personal del Taller
            </h1>
            <p class="text-sm text-gray-500 mt-2">Registra a las personas que trabajan en tu taller.</p>
        </div>

        <button type="button" onclick="abrirModalCrear()"
            class="inline-flex items-center gap-2 px-5 py-3 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-orange-500/30 transition">
            <i class="fa-solid fa-plus"></i>
            Nuevo miembro
        </button>
    </div>

    {{-- Mensajes --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!empty($sinTaller))
        <div
            class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl flex items-center gap-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i>
            <span class="text-sm">Tu usuario no está asociado a ningún taller. Contacta al administrador.</span>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('taller.personal') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search"
                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fa-solid fa-search"></i>
                    </span>
                    <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                        placeholder="Nombre, especialidad, email..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                </div>
            </div>

            <div class="min-w-[150px]">
                <label for="estado"
                    class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Estado</label>
                <select id="estado" name="estado"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm bg-white">
                    <option value="">Todos</option>
                    <option value="activo" {{ ($filterEstado ?? '') === 'activo' ? 'selected' : '' }}>Activos</option>
                    <option value="inactivo" {{ ($filterEstado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-filter mr-2"></i> Filtrar
                </button>
                <a href="{{ route('taller.personal') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-rotate-right mr-2"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Contador --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            <span class="font-semibold">{{ $total ?? 0 }}</span> miembros encontrados
        </p>
    </div>

    {{-- Grid de personal --}}
    @if (empty($personal))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-users text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Aún no hay personal registrado</h3>
                <p class="text-sm text-gray-500">Agrega a los miembros de tu equipo para tenerlos organizados.</p>
                <button type="button" onclick="abrirModalCrear()"
                    class="mt-3 px-5 py-2.5 bg-brand-orange hover:bg-orange-600 text-white rounded-xl font-semibold text-sm transition">
                    <i class="fa-solid fa-plus mr-2"></i> Agregar primer miembro
                </button>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($personal as $persona)
                @php
                    $activo = $persona['activo'] ?? true;
                    $iniciales = '';
                    foreach (preg_split('/\s+/', trim($persona['nombre'] ?? 'U')) as $p) {
                        if (!empty($p)) {
                            $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
                        }
                        if (mb_strlen($iniciales) >= 2) {
                            break;
                        }
                    }
                    if (empty($iniciales)) {
                        $iniciales = 'U';
                    }
                @endphp

                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group relative {{ !$activo ? 'opacity-70' : '' }}">

                    {{-- Toggle activo --}}
                    <div class="absolute top-4 right-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer toggle-activo" data-id="{{ $persona['id'] }}"
                                {{ $activo ? 'checked' : '' }}>
                            <div
                                class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer
                                        peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                        after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4
                                        after:transition-all peer-checked:bg-brand-blue relative">
                            </div>
                        </label>
                    </div>

                    {{-- Avatar --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-blue to-brand-darkblue text-white flex items-center justify-center font-bold text-lg shadow-md">
                            {{ $iniciales }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 truncate">{{ $persona['nombre'] ?? 'Sin nombre' }}</h3>
                            @if (!empty($persona['puesto']))
                                <p class="text-xs text-gray-500 truncate">{{ $persona['puesto'] }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="space-y-1.5 mb-4">
                        @if (!empty($persona['especialidad']))
                            <p class="text-xs text-gray-600 flex items-center gap-2 truncate">
                                <i class="fa-solid fa-screwdriver-wrench text-brand-orange text-[11px]"></i>
                                <span class="truncate">{{ $persona['especialidad'] }}</span>
                            </p>
                        @endif
                        @if (!empty($persona['telefono']))
                            <p class="text-xs text-gray-600 flex items-center gap-2 truncate">
                                <i class="fa-solid fa-phone text-brand-blue text-[11px]"></i>
                                {{ $persona['telefono'] }}
                            </p>
                        @endif
                        @if (!empty($persona['email']))
                            <p class="text-xs text-gray-600 flex items-center gap-2 truncate">
                                <i class="fa-solid fa-envelope text-brand-blue text-[11px]"></i>
                                <span class="truncate">{{ $persona['email'] }}</span>
                            </p>
                        @endif
                    </div>

                    @if (!empty($persona['notas']))
                        <p class="text-[11px] text-gray-400 italic line-clamp-2 mb-3">
                            "{{ $persona['notas'] }}"
                        </p>
                    @endif

                    {{-- Acciones --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                        <button type="button"
                            class="btn-editar flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-brand-blue hover:text-white text-gray-700 rounded-lg text-xs font-semibold transition"
                            data-persona='@json($persona)'>
                            <i class="fa-solid fa-pen text-[10px]"></i> Editar
                        </button>
                        <button type="button"
                            class="btn-eliminar w-9 h-9 inline-flex items-center justify-center bg-gray-100 hover:bg-red-500 hover:text-white text-gray-600 rounded-lg transition"
                            data-id="{{ $persona['id'] }}" data-nombre="{{ $persona['nombre'] ?? 'miembro' }}"
                            title="Eliminar">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- MODAL CREAR/EDITAR --}}
    {{-- ============================================== --}}
    <div id="modal-personal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="cerrarModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg my-8 relative">
                <form id="form-personal" method="POST" action="{{ route('taller.personal.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 id="modal-title" class="text-lg font-bold text-gray-900">Nuevo miembro</h3>
                        <button type="button" onclick="cerrarModal()"
                            class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-500 flex items-center justify-center transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" id="input-nombre" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Especialidad
                            </label>
                            <input type="text" name="especialidad" id="input-especialidad"
                                placeholder="Ej: Mecánica general, Electricidad, Hojalatería..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Puesto / Rol
                            </label>
                            <input type="text" name="puesto" id="input-puesto"
                                placeholder="Ej: Jefe de taller, Ayudante, Recepcionista..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                    Teléfono
                                </label>
                                <input type="text" name="telefono" id="input-telefono"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                    Email
                                </label>
                                <input type="email" name="email" id="input-email"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                Notas
                            </label>
                            <textarea name="notas" id="input-notas" rows="3" placeholder="Información adicional sobre este miembro..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-blue text-sm resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" onclick="cerrarModal()"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-blue hover:bg-brand-darkblue text-white rounded-xl font-semibold text-sm transition shadow-md shadow-blue-500/20">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            <span id="btn-submit-text">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('modal-personal');
                const form = document.getElementById('form-personal');
                const formMethod = document.getElementById('form-method');
                const modalTitle = document.getElementById('modal-title');
                const btnSubmitText = document.getElementById('btn-submit-text');

                const inputNombre = document.getElementById('input-nombre');
                const inputEspecialidad = document.getElementById('input-especialidad');
                const inputPuesto = document.getElementById('input-puesto');
                const inputTelefono = document.getElementById('input-telefono');
                const inputEmail = document.getElementById('input-email');
                const inputNotas = document.getElementById('input-notas');

                const storeUrl = '{{ route('taller.personal.store') }}';
                const updateUrlBase = '{{ route('taller.personal.update', ['id' => '__ID__']) }}';

                window.abrirModalCrear = function() {
                    form.reset();
                    form.action = storeUrl;
                    formMethod.value = 'POST';
                    modalTitle.textContent = 'Nuevo miembro';
                    btnSubmitText.textContent = 'Agregar';
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    setTimeout(() => inputNombre.focus(), 100);
                };

                window.cerrarModal = function() {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                };

                // Botones editar
                document.querySelectorAll('.btn-editar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const p = JSON.parse(this.dataset.persona);

                        form.action = updateUrlBase.replace('__ID__', p.id);
                        formMethod.value = 'PUT';
                        modalTitle.textContent = 'Editar miembro';
                        btnSubmitText.textContent = 'Guardar cambios';

                        inputNombre.value = p.nombre || '';
                        inputEspecialidad.value = p.especialidad || '';
                        inputPuesto.value = p.puesto || '';
                        inputTelefono.value = p.telefono || '';
                        inputEmail.value = p.email || '';
                        inputNotas.value = p.notas || '';

                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        setTimeout(() => inputNombre.focus(), 100);
                    });
                });

                // Toggle activo
                document.querySelectorAll('.toggle-activo').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const id = this.dataset.id;
                        const el = this;
                        el.disabled = true;

                        fetch(`/taller/personal/${id}/toggle-activo`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    activo: el.checked
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast(data.message, 'success');
                                } else {
                                    el.checked = !el.checked;
                                    showToast(data.error || 'Error', 'error');
                                }
                            })
                            .catch(() => {
                                el.checked = !el.checked;
                                showToast('Error de conexión', 'error');
                            })
                            .finally(() => el.disabled = false);
                    });
                });

                // Eliminar
                document.querySelectorAll('.btn-eliminar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const nombre = this.dataset.nombre;

                        if (!confirm(`¿Eliminar a "${nombre}" del personal?`)) return;

                        const formDel = document.createElement('form');
                        formDel.method = 'POST';
                        formDel.action = `/taller/personal/${id}`;
                        formDel.innerHTML = `
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                        `;
                        document.body.appendChild(formDel);
                        formDel.submit();
                    });
                });

                // Cerrar modal con ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        cerrarModal();
                    }
                });

                function showToast(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-50 border-green-200 text-green-700',
                        error: 'bg-red-50 border-red-200 text-red-700',
                        info: 'bg-blue-50 border-blue-200 text-brand-blue',
                    };
                    const icons = {
                        success: 'fa-circle-check text-green-500',
                        error: 'fa-circle-exclamation text-red-500',
                        info: 'fa-circle-info text-brand-blue',
                    };

                    const toast = document.createElement('div');
                    toast.className =
                        `fixed top-4 right-4 z-[200] px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 border ${colors[type]}`;
                    toast.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span class="text-sm">${message}</span>`;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }, 3000);
                }
            });
        </script>
    @endpush
@endsection
