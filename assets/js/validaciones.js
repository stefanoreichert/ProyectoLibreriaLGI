// Validaciones específicas para formularios
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar validaciones
    initFormValidations();
    
    // Validaciones en tiempo real
    initRealTimeValidations();
    
    // Formateo automático de campos
    initFieldFormatting();
});

// Inicializar validaciones de formularios
function initFormValidations() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateFormFields(this)) {
                e.preventDefault();
                showValidationErrors();
            }
        });
    });
}

// Validar todos los campos de un formulario
function validateFormFields(form) {
    let isValid = true;
    const errors = [];
    
    // Limpiar errores previos
    clearFormErrors(form);
    
    // Validar campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!validateRequired(field)) {
            isValid = false;
            addFieldError(field, 'Este campo es obligatorio');
        }
    });
    
    // Validar emails
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !validateEmail(field.value)) {
            isValid = false;
            addFieldError(field, 'Ingrese un email válido');
        }
    });
    
    // Validar contraseñas
    const passwordFields = form.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        if (field.value && !validatePassword(field.value, field)) {
            isValid = false;
        }
    });
    
    // Validar confirmación de contraseña
    const confirmPasswordField = form.querySelector('input[name="confirmar_password"]');
    const passwordField = form.querySelector('input[name="password"]');
    if (confirmPasswordField && passwordField) {
        if (!validatePasswordConfirmation(passwordField.value, confirmPasswordField.value)) {
            isValid = false;
            addFieldError(confirmPasswordField, 'Las contraseñas no coinciden');
        }
    }
    
    // Validar teléfonos
    const phoneFields = form.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        if (field.value && !validatePhone(field.value)) {
            isValid = false;
            addFieldError(field, 'Ingrese un número de teléfono válido');
        }
    });
    
    // Validar ISBNs (solo formato básico, no dígito de control)
    const isbnFields = form.querySelectorAll('input[name="isbn"]');
    isbnFields.forEach(field => {
        if (field.value && !validateISBNFormat(field.value)) {
            isValid = false;
            addFieldError(field, 'El ISBN debe tener 10 o 13 dígitos');
        }
    });
    
    // Validar números
    const numberFields = form.querySelectorAll('input[type="number"]');
    numberFields.forEach(field => {
        if (field.value && !validateNumber(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Validaciones específicas
function validateRequired(field) {
    const value = field.value.trim();
    
    if (field.type === 'checkbox' || field.type === 'radio') {
        return field.checked;
    }
    
    return value !== '';
}

function validateEmail(email) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return regex.test(email);
}

function validatePassword(password, field) {
    const minLength = parseInt(field.getAttribute('minlength')) || 6;
    const errors = [];
    
    if (password.length < minLength) {
        errors.push(`Mínimo ${minLength} caracteres`);
    }
    
    if (!/[A-Z]/.test(password) && field.hasAttribute('data-require-uppercase')) {
        errors.push('Debe contener al menos una mayúscula');
    }
    
    if (!/[a-z]/.test(password) && field.hasAttribute('data-require-lowercase')) {
        errors.push('Debe contener al menos una minúscula');
    }
    
    if (!/\d/.test(password) && field.hasAttribute('data-require-number')) {
        errors.push('Debe contener al menos un número');
    }
    
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password) && field.hasAttribute('data-require-special')) {
        errors.push('Debe contener al menos un carácter especial');
    }
    
    if (errors.length > 0) {
        addFieldError(field, errors.join('. '));
        return false;
    }
    
    return true;
}

function validatePasswordConfirmation(password, confirmation) {
    return password === confirmation;
}

function validatePhone(phone) {
    // Remover espacios y caracteres especiales
    const cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
    
    // Validar formato básico (puede empezar con + y tener entre 7 y 15 dígitos)
    const regex = /^(\+\d{1,3}[- ]?)?\d{7,14}$/;
    return regex.test(cleanPhone);
}

function validateISBN(isbn) {
    // Remover guiones y espacios
    const cleanISBN = isbn.replace(/[-\s]/g, '');
    
    // Validar ISBN-10 o ISBN-13
    if (cleanISBN.length === 10) {
        return validateISBN10(cleanISBN);
    } else if (cleanISBN.length === 13) {
        return validateISBN13(cleanISBN);
    }
    
    return false;
}

// Validación de formato simple (solo longitud)
function validateISBNFormat(isbn) {
    // Remover guiones y espacios
    const cleanISBN = isbn.replace(/[-\s]/g, '');
    
    // Verificar que solo contenga números (y X para ISBN-10)
    if (!/^[\dX]+$/.test(cleanISBN)) {
        return false;
    }
    
    // Aceptar ISBNs de 10 o 13 dígitos
    return cleanISBN.length === 10 || cleanISBN.length === 13;
}

function validateISBN10(isbn) {
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        if (!/\d/.test(isbn[i])) return false;
        sum += parseInt(isbn[i]) * (10 - i);
    }
    
    const checkDigit = isbn[9];
    const calculatedCheck = (11 - (sum % 11)) % 11;
    
    return checkDigit === (calculatedCheck === 10 ? 'X' : calculatedCheck.toString());
}

function validateISBN13(isbn) {
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        if (!/\d/.test(isbn[i])) return false;
        sum += parseInt(isbn[i]) * (i % 2 === 0 ? 1 : 3);
    }
    
    const checkDigit = parseInt(isbn[12]);
    const calculatedCheck = (10 - (sum % 10)) % 10;
    
    return checkDigit === calculatedCheck;
}

function validateNumber(field) {
    const value = parseFloat(field.value);
    const min = parseFloat(field.getAttribute('min'));
    const max = parseFloat(field.getAttribute('max'));
    const step = parseFloat(field.getAttribute('step'));
    
    if (isNaN(value)) {
        addFieldError(field, 'Ingrese un número válido');
        return false;
    }
    
    if (!isNaN(min) && value < min) {
        addFieldError(field, `El valor mínimo es ${min}`);
        return false;
    }
    
    if (!isNaN(max) && value > max) {
        addFieldError(field, `El valor máximo es ${max}`);
        return false;
    }
    
    if (!isNaN(step) && step > 0) {
        const remainder = (value - (min || 0)) % step;
        if (Math.abs(remainder) > 0.001 && Math.abs(remainder - step) > 0.001) {
            addFieldError(field, `El valor debe ser múltiplo de ${step}`);
            return false;
        }
    }
    
    return true;
}

// Validaciones en tiempo real
function initRealTimeValidations() {
    // Email en tiempo real
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            clearFieldError(this);
            if (this.value && !validateEmail(this.value)) {
                addFieldError(this, 'Ingrese un email válido');
            }
        });
    });
    
    // Contraseñas en tiempo real
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            clearFieldError(this);
            if (this.value) {
                validatePassword(this.value, this);
            }
        });
    });
    
    // Confirmación de contraseña
    const confirmPasswordFields = document.querySelectorAll('input[name="confirmar_password"]');
    confirmPasswordFields.forEach(field => {
        field.addEventListener('input', function() {
            const passwordField = this.form.querySelector('input[name="password"]');
            clearFieldError(this);
            if (passwordField && this.value) {
                if (!validatePasswordConfirmation(passwordField.value, this.value)) {
                    addFieldError(this, 'Las contraseñas no coinciden');
                }
            }
        });
    });
    
    // ISBN en tiempo real (solo formato, no dígito de control)
    const isbnFields = document.querySelectorAll('input[name="isbn"]');
    isbnFields.forEach(field => {
        field.addEventListener('blur', function() {
            clearFieldError(this);
            if (this.value && !validateISBNFormat(this.value)) {
                addFieldError(this, 'El ISBN debe tener 10 o 13 dígitos');
            }
        });
    });
}

// Formateo automático de campos
function initFieldFormatting() {
    // Formatear teléfonos
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = formatPhoneNumber(this.value);
        });
    });
    
    // NO formatear ISBNs automáticamente - dejar que el usuario escriba libremente
    // Los ISBNs se pueden escribir con o sin guiones
    
    // Solo números en campos numéricos
    const numericFields = document.querySelectorAll('input[data-numeric-only]');
    numericFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // Solo letras en campos de texto
    const textOnlyFields = document.querySelectorAll('input[data-text-only]');
    textOnlyFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });
}

// Funciones de formateo
function formatPhoneNumber(phone) {
    // Remover caracteres no numéricos excepto +
    let cleaned = phone.replace(/[^\d+]/g, '');
    
    // Formatear según longitud
    if (cleaned.length >= 10) {
        if (cleaned.startsWith('+')) {
            // Formato internacional
            return cleaned.replace(/(\+\d{1,3})(\d{3})(\d{3})(\d{4})/, '$1 $2 $3 $4');
        } else {
            // Formato nacional
            return cleaned.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
    }
    
    return cleaned;
}

function formatISBN(isbn) {
    // Remover caracteres no alfanuméricos
    let cleaned = isbn.replace(/[^0-9X]/g, '');
    
    // Formatear ISBN-13
    if (cleaned.length <= 13) {
        return cleaned.replace(/(\d{3})(\d{1})(\d{3})(\d{3})(\d{3})(\d{1})/, '$1-$2-$3-$4-$5-$6');
    }
    
    // Formatear ISBN-10
    if (cleaned.length <= 10) {
        return cleaned.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{1}|X)/, '$1-$2-$3-$4-$5');
    }
    
    return cleaned;
}

// Manejo de errores
function addFieldError(field, message) {
    field.classList.add('error');
    
    // Remover mensaje de error previo
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Agregar nuevo mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #e74c3c;
        font-size: 0.85rem;
        margin-top: 5px;
        display: block;
    `;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function clearFormErrors(form) {
    const errorFields = form.querySelectorAll('.error');
    const errorMessages = form.querySelectorAll('.field-error');
    
    errorFields.forEach(field => field.classList.remove('error'));
    errorMessages.forEach(message => message.remove());
}

function showValidationErrors() {
    const firstError = document.querySelector('.error');
    if (firstError) {
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Funciones públicas
window.FormValidations = {
    validateFormFields,
    validateEmail,
    validatePhone,
    validateISBN,
    addFieldError,
    clearFieldError,
    clearFormErrors
};