// Funcionalidades de búsqueda con AJAX
document.addEventListener('DOMContentLoaded', function() {
    initSearchFunctionality();
    initAutocomplete();
    initAdvancedFilters();
});

// Inicializar funcionalidad de búsqueda
function initSearchFunctionality() {
    const searchForms = document.querySelectorAll('.search-form');
    
    searchForms.forEach(form => {
        const searchInput = form.querySelector('.search-input, input[name="busqueda"]');
        if (searchInput) {
            // Búsqueda en tiempo real con debounce
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        performSearch(this.value, form);
                    }
                }, 500);
            });
            
            // Limpiar búsqueda con Escape
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    performSearch('', form);
                }
            });
        }
    });
}

// Realizar búsqueda
function performSearch(query, form) {
    const currentUrl = new URL(window.location);
    
    if (query.trim() === '') {
        currentUrl.searchParams.delete('busqueda');
    } else {
        currentUrl.searchParams.set('busqueda', query);
    }
    
    // Resetear página a 1
    currentUrl.searchParams.delete('pagina');
    
    // Solo actualizar si es diferente a la URL actual
    if (currentUrl.toString() !== window.location.toString()) {
        window.history.pushState({}, '', currentUrl);
        loadSearchResults();
    }
}

// Cargar resultados de búsqueda
function loadSearchResults() {
    const tableContainer = document.querySelector('.table-container');
    const pagination = document.querySelector('.pagination');
    const resultsInfo = document.querySelector('.results-info');
    
    if (!tableContainer) return;
    
    // Mostrar loading
    showSearchLoading(tableContainer);
    
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Actualizar tabla
        const newTable = doc.querySelector('.table-container');
        if (newTable) {
            tableContainer.innerHTML = newTable.innerHTML;
        }
        
        // Actualizar paginación
        const newPagination = doc.querySelector('.pagination');
        if (pagination && newPagination) {
            pagination.innerHTML = newPagination.innerHTML;
        } else if (pagination) {
            pagination.style.display = 'none';
        }
        
        // Actualizar info de resultados
        const newResultsInfo = doc.querySelector('.results-info');
        if (resultsInfo && newResultsInfo) {
            resultsInfo.innerHTML = newResultsInfo.innerHTML;
        }
        
        // Reinicializar eventos de tabla
        initTableEvents();
    })
    .catch(error => {
        console.error('Error en búsqueda:', error);
        showSearchError(tableContainer);
    });
}

// Mostrar loading en búsqueda
function showSearchLoading(container) {
    container.innerHTML = `
        <div class="search-loading">
            <div class="loading-spinner"></div>
            <p>Buscando...</p>
        </div>
    `;
}

// Mostrar error en búsqueda
function showSearchError(container) {
    container.innerHTML = `
        <div class="search-error">
            <p>Error al realizar la búsqueda. Por favor, intente nuevamente.</p>
            <button onclick="window.location.reload()" class="btn btn-primary">Recargar</button>
        </div>
    `;
}

// Inicializar autocompletado
function initAutocomplete() {
    const autocompleteInputs = document.querySelectorAll('.autocomplete-input');
    
    autocompleteInputs.forEach(input => {
        let timeout;
        let currentRequest = null;
        
        input.addEventListener('input', function() {
            const query = this.value.trim();
            const resultsContainer = this.parentNode.querySelector('.autocomplete-results');
            
            if (!resultsContainer) return;
            
            clearTimeout(timeout);
            
            if (currentRequest) {
                currentRequest.abort();
            }
            
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            timeout = setTimeout(() => {
                performAutocomplete(this, query, resultsContainer);
            }, 300);
        });
        
        // Navegación con teclado
        input.addEventListener('keydown', function(e) {
            const resultsContainer = this.parentNode.querySelector('.autocomplete-results');
            if (!resultsContainer || resultsContainer.style.display === 'none') return;
            
            const items = resultsContainer.querySelectorAll('.autocomplete-item');
            const currentActive = resultsContainer.querySelector('.autocomplete-item.active');
            let activeIndex = -1;
            
            if (currentActive) {
                activeIndex = Array.from(items).indexOf(currentActive);
            }
            
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    updateActiveItem(items, activeIndex);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, -1);
                    updateActiveItem(items, activeIndex);
                    break;
                    
                case 'Enter':
                    e.preventDefault();
                    if (currentActive && !currentActive.classList.contains('disabled')) {
                        currentActive.click();
                    }
                    break;
                    
                case 'Escape':
                    resultsContainer.style.display = 'none';
                    break;
            }
        });
    });
    
    // Cerrar autocompletado al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-container')) {
            document.querySelectorAll('.autocomplete-results').forEach(container => {
                container.style.display = 'none';
            });
        }
    });
}

// Realizar autocompletado
function performAutocomplete(input, query, resultsContainer) {
    const endpoint = getAutocompleteEndpoint(input);
    if (!endpoint) return;
    
    const url = `${endpoint}?q=${encodeURIComponent(query)}&limit=10`;
    
    currentRequest = fetch(url)
        .then(response => response.json())
        .then(data => {
            displayAutocompleteResults(data, resultsContainer, input);
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Error en autocompletado:', error);
            }
        });
}

// Obtener endpoint de autocompletado
function getAutocompleteEndpoint(input) {
    const id = input.id;
    
    if (id.includes('usuario')) {
        return 'ajax_validar.php?action=buscar_usuarios';
    } else if (id.includes('libro')) {
        return '../libros/buscar.php';
    }
    
    return input.getAttribute('data-autocomplete-url');
}

// Mostrar resultados de autocompletado
function displayAutocompleteResults(data, container, input) {
    container.innerHTML = '';
    
    if (!data.resultados || data.resultados.length === 0) {
        container.innerHTML = '<div class="autocomplete-item disabled">No se encontraron resultados</div>';
        container.style.display = 'block';
        return;
    }
    
    data.resultados.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        
        if (item.disponible === false || item.prestamos_activos >= 3) {
            div.classList.add('disabled');
        }
        
        div.innerHTML = formatAutocompleteItem(item, input);
        
        if (!div.classList.contains('disabled')) {
            div.addEventListener('click', () => {
                selectAutocompleteItem(item, input);
                container.style.display = 'none';
            });
        }
        
        container.appendChild(div);
    });
    
    container.style.display = 'block';
}

// Formatear item de autocompletado
function formatAutocompleteItem(item, input) {
    const id = input.id;
    
    if (id.includes('usuario')) {
        return `
            <strong>${item.nombre}</strong><br>
            <small>${item.email} - Préstamos activos: ${item.prestamos_activos || 0}</small>
        `;
    } else if (id.includes('libro')) {
        return `
            <strong>${item.titulo}</strong><br>
            <small>${item.autor} - Disponibles: ${item.disponibles || 0}/${item.stock || 0}</small>
        `;
    }
    
    return `<strong>${item.nombre || item.titulo}</strong>`;
}

// Seleccionar item de autocompletado
function selectAutocompleteItem(item, input) {
    const id = input.id;
    
    if (id.includes('usuario')) {
        input.value = item.nombre;
        const hiddenInput = document.getElementById('usuario_id');
        if (hiddenInput) {
            hiddenInput.value = item.id;
        }
        
        // Mostrar información del usuario
        const infoContainer = document.getElementById('usuario_info');
        if (infoContainer) {
            infoContainer.innerHTML = `
                <div class="info-card">
                    <strong>${item.nombre}</strong><br>
                    Email: ${item.email}<br>
                    Préstamos activos: ${item.prestamos_activos || 0}/3
                </div>
            `;
        }
    } else if (id.includes('libro')) {
        input.value = item.titulo;
        const hiddenInput = document.getElementById('libro_id');
        if (hiddenInput) {
            hiddenInput.value = item.id;
        }
        
        // Mostrar información del libro
        const infoContainer = document.getElementById('libro_info');
        if (infoContainer) {
            infoContainer.innerHTML = `
                <div class="info-card">
                    <strong>${item.titulo}</strong><br>
                    Autor: ${item.autor}<br>
                    ISBN: ${item.isbn}<br>
                    Disponibles: ${item.disponibles || 0} de ${item.stock || 0}
                </div>
            `;
        }
    }
    
    // Disparar evento personalizado
    input.dispatchEvent(new CustomEvent('autocomplete:select', { detail: item }));
}

// Actualizar item activo en autocompletado
function updateActiveItem(items, activeIndex) {
    items.forEach((item, index) => {
        item.classList.toggle('active', index === activeIndex);
    });
}

// Inicializar filtros avanzados
function initAdvancedFilters() {
    const filterForms = document.querySelectorAll('.filters-form');
    
    filterForms.forEach(form => {
        const selects = form.querySelectorAll('select');
        
        selects.forEach(select => {
            select.addEventListener('change', function() {
                updateFilters(form);
            });
        });
    });
}

// Actualizar filtros
function updateFilters(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.set(key, value);
        }
    }
    
    // Mantener la búsqueda actual
    const currentSearch = new URLSearchParams(window.location.search).get('busqueda');
    if (currentSearch) {
        params.set('busqueda', currentSearch);
    }
    
    // Actualizar URL
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({}, '', newUrl);
    
    // Recargar resultados
    loadSearchResults();
}

// Inicializar eventos de tabla
function initTableEvents() {
    // Reinicializar tooltips
    if (window.LibreriaLGI && window.LibreriaLGI.initTooltips) {
        window.LibreriaLGI.initTooltips();
    }
    
    // Reinicializar confirmaciones
    if (window.LibreriaLGI && window.LibreriaLGI.initConfirmations) {
        window.LibreriaLGI.initConfirmations();
    }
}

// Exportar búsqueda en tiempo real para uso manual
function liveSearch(input, endpoint, callback) {
    let timeout;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            callback([]);
            return;
        }
        
        timeout = setTimeout(() => {
            fetch(`${endpoint}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => callback(data.resultados || []))
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    callback([]);
                });
        }, 300);
    });
}

// Agregar estilos para autocompletado
const style = document.createElement('style');
style.textContent = `
    .search-loading {
        text-align: center;
        padding: 40px;
        color: #666;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .search-error {
        text-align: center;
        padding: 40px;
        color: #e74c3c;
    }
    
    .autocomplete-item.active {
        background-color: #3498db !important;
        color: white !important;
    }
    
    .autocomplete-item.active small {
        color: rgba(255, 255, 255, 0.8) !important;
    }
`;
document.head.appendChild(style);

// Funciones públicas
window.SearchFunctions = {
    performSearch,
    liveSearch,
    selectAutocompleteItem
};