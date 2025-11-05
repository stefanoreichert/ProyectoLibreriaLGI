// Funciones principales del sistema
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    initTooltips();
    
    // Inicializar confirmaciones
    initConfirmations();
    
    // Manejar dropdowns móviles
    initMobileDropdowns();
    
    // Auto-hide alerts después de 5 segundos
    autoHideAlerts();
    
    // Inicializar contadores animados
    initAnimatedCounters();
});

// Inicializar tooltips simples
function initTooltips() {
    const elementsWithTitle = document.querySelectorAll('[title]');
    elementsWithTitle.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (title) {
                showTooltip(this, title);
            }
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

function showTooltip(element, text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 10000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    // Remover el atributo title temporalmente
    element.setAttribute('data-original-title', text);
    element.removeAttribute('title');
}

function hideTooltip() {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
    
    // Restaurar títulos originales
    const elementsWithDataTitle = document.querySelectorAll('[data-original-title]');
    elementsWithDataTitle.forEach(element => {
        element.setAttribute('title', element.getAttribute('data-original-title'));
        element.removeAttribute('data-original-title');
    });
}

// Inicializar confirmaciones
function initConfirmations() {
    const deleteLinks = document.querySelectorAll('a[href*="eliminar"]');
    deleteLinks.forEach(link => {
        if (!link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                const confirmMessage = this.getAttribute('data-confirm') || 
                    '¿Está seguro de que desea eliminar este elemento?';
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        }
    });
}

// Manejar dropdowns en móviles
function initMobileDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                }
            });
        }
    });
    
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
}

// Auto-ocultar alertas
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-success, .alert-info');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
}

// Contadores animados
function initAnimatedCounters() {
    const counters = document.querySelectorAll('.stat-number, .stat-card h3');
    
    const animateCounter = (element) => {
        const target = parseInt(element.textContent);
        const duration = 1000;
        const start = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(progress * target);
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = target;
            }
        };
        
        requestAnimationFrame(animate);
    };
    
    // Intersección observer para animar cuando sea visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.hasAttribute('data-animated')) {
                entry.target.setAttribute('data-animated', 'true');
                animateCounter(entry.target);
            }
        });
    });
    
    counters.forEach(counter => {
        if (parseInt(counter.textContent) > 0) {
            observer.observe(counter);
        }
    });
}

// Funciones para formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Función para limpiar formularios
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.querySelectorAll('.error').forEach(field => {
            field.classList.remove('error');
        });
    }
}

// Función para mostrar/ocultar contraseñas
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

// Función para formatear números
function formatNumber(num) {
    return new Intl.NumberFormat('es-ES').format(num);
}

// Función para formatear fechas
function formatDate(dateString, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    return new Date(dateString).toLocaleDateString('es-ES', finalOptions);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Función para mostrar mensajes temporales
function showMessage(message, type = 'info', duration = 3000) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type} message-toast`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            messageDiv.remove();
        }, 300);
    }, duration);
}

// Funciones para el manejo de tablas
function sortTable(columnIndex, tableId = null) {
    const table = tableId ? document.getElementById(tableId) : document.querySelector('.data-table');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isNumeric = !isNaN(parseFloat(rows[0].cells[columnIndex].textContent));
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].textContent.trim();
        const bVal = b.cells[columnIndex].textContent.trim();
        
        if (isNumeric) {
            return parseFloat(aVal) - parseFloat(bVal);
        } else {
            return aVal.localeCompare(bVal);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Función para filtrar tablas
function filterTable(searchTerm, tableId = null) {
    const table = tableId ? document.getElementById(tableId) : document.querySelector('.data-table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    searchTerm = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Función para exportar tabla a CSV
function exportTableToCSV(filename = 'datos.csv', tableId = null) {
    const table = tableId ? document.getElementById(tableId) : document.querySelector('.data-table');
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tr'));
    const csv = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
    }).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showMessage('Copiado al portapapeles', 'success', 2000);
    }).catch(() => {
        showMessage('Error al copiar', 'error', 2000);
    });
}

// Función para validar email
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para validar teléfono
function isValidPhone(phone) {
    const regex = /^[\+]?[1-9][\d]{0,15}$/;
    return regex.test(phone.replace(/\s/g, ''));
}

// Agregar estilos para animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .error {
        border-color: #e74c3c !important;
        box-shadow: 0 0 5px rgba(231, 76, 60, 0.3) !important;
    }
    
    .message-toast {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
`;
document.head.appendChild(style);

// Exportar funciones para uso global
window.LibreriaLGI = {
    validateForm,
    clearForm,
    togglePassword,
    formatNumber,
    formatDate,
    confirmAction,
    showMessage,
    sortTable,
    filterTable,
    exportTableToCSV,
    copyToClipboard,
    isValidEmail,
    isValidPhone
};