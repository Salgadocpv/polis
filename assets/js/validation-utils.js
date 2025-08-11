/**
 * Utilitários de Validação para Polis Engenharia
 */

class ValidationUtils {
    
    /**
     * Valida CPF usando o algoritmo oficial
     * @param {string} cpf - CPF para validar
     * @returns {boolean} - true se válido, false caso contrário
     */
    static validateCPF(cpf) {
        // Remove caracteres não numéricos
        cpf = cpf.replace(/\D/g, '');

        // Verifica se tem 11 dígitos
        if (cpf.length !== 11) return false;

        // Verifica se todos os dígitos são iguais (CPFs inválidos conhecidos)
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validação do primeiro dígito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = soma % 11;
        let digito1 = resto < 2 ? 0 : 11 - resto;

        if (parseInt(cpf.charAt(9)) !== digito1) return false;

        // Validação do segundo dígito verificador
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = soma % 11;
        let digito2 = resto < 2 ? 0 : 11 - resto;

        if (parseInt(cpf.charAt(10)) !== digito2) return false;

        return true;
    }

    /**
     * Valida CNPJ usando o algoritmo oficial
     * @param {string} cnpj - CNPJ para validar
     * @returns {boolean} - true se válido, false caso contrário
     */
    static validateCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');

        if (cnpj.length !== 14) return false;

        // Verifica se todos os dígitos são iguais
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        // Validação do primeiro dígito verificador
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;

        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }

        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado !== parseInt(digitos.charAt(0))) return false;

        // Validação do segundo dígito verificador
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;

        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }

        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado !== parseInt(digitos.charAt(1))) return false;

        return true;
    }

    /**
     * Valida email usando regex
     * @param {string} email - Email para validar
     * @returns {boolean} - true se válido, false caso contrário
     */
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Valida telefone brasileiro
     * @param {string} phone - Telefone para validar
     * @returns {boolean} - true se válido, false caso contrário
     */
    static validatePhone(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        // Aceita formatos: (XX) XXXX-XXXX ou (XX) XXXXX-XXXX
        return cleanPhone.length === 10 || cleanPhone.length === 11;
    }

    /**
     * Valida se a data não é futura
     * @param {string} dateStr - Data no formato YYYY-MM-DD
     * @returns {boolean} - true se não for futura, false caso contrário
     */
    static validateDateNotFuture(dateStr) {
        const inputDate = new Date(dateStr);
        const today = new Date();
        today.setHours(23, 59, 59, 999); // Fim do dia atual
        return inputDate <= today;
    }

    /**
     * Valida se o valor é um número positivo
     * @param {string|number} value - Valor para validar
     * @returns {boolean} - true se for número positivo, false caso contrário
     */
    static validatePositiveNumber(value) {
        const num = parseFloat(value);
        return !isNaN(num) && num > 0;
    }

    /**
     * Valida se o campo não está vazio (trim)
     * @param {string} value - Valor para validar
     * @returns {boolean} - true se não estiver vazio, false caso contrário
     */
    static validateRequired(value) {
        return value && value.toString().trim().length > 0;
    }

    /**
     * Valida tamanho mínimo de string
     * @param {string} value - Valor para validar
     * @param {number} minLength - Tamanho mínimo
     * @returns {boolean} - true se atender ao tamanho mínimo, false caso contrário
     */
    static validateMinLength(value, minLength) {
        return value && value.toString().trim().length >= minLength;
    }

    /**
     * Valida tamanho máximo de string
     * @param {string} value - Valor para validar
     * @param {number} maxLength - Tamanho máximo
     * @returns {boolean} - true se não exceder o tamanho máximo, false caso contrário
     */
    static validateMaxLength(value, maxLength) {
        return !value || value.toString().trim().length <= maxLength;
    }
}

// Funções de máscara reutilizáveis
class MaskUtils {
    /**
     * Aplica máscara de CPF
     * @param {string} value - Valor para aplicar máscara
     * @returns {string} - Valor com máscara aplicada
     */
    static cpfMask(value) {
        value = value.replace(/\D/g, '');
        value = value.slice(0, 11);
        if (value.length > 9) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{3})(\d{3})(\d{3}).*/, '$1.$2.$3');
        } else if (value.length > 3) {
            value = value.replace(/^(\d{3})(\d{3}).*/, '$1.$2');
        }
        return value;
    }

    /**
     * Aplica máscara de telefone
     * @param {string} value - Valor para aplicar máscara
     * @returns {string} - Valor com máscara aplicada
     */
    static phoneMask(value) {
        value = value.replace(/\D/g, '');
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        if (value.length > 10) { // Formato (XX) XXXXX-XXXX
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (value.length > 6) { // Formato (XX) XXXX-XXXX
            value = value.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
        } else if (value.length > 2) { // Formato (XX) XXXX
            value = value.replace(/^(\d{2})(\d*)/, '($1) $2');
        }
        return value;
    }

    /**
     * Aplica máscara de CNPJ
     * @param {string} value - Valor para aplicar máscara
     * @returns {string} - Valor com máscara aplicada
     */
    static cnpjMask(value) {
        value = value.replace(/\D/g, '');
        value = value.slice(0, 14);
        if (value.length > 12) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
        } else if (value.length > 8) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4}).*/, '$1.$2.$3/$4');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3}).*/, '$1.$2.$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{3}).*/, '$1.$2');
        }
        return value;
    }

    /**
     * Aplica máscara de CEP
     * @param {string} value - Valor para aplicar máscara
     * @returns {string} - Valor com máscara aplicada
     */
    static cepMask(value) {
        value = value.replace(/\D/g, '');
        value = value.slice(0, 8);
        if (value.length > 5) {
            value = value.replace(/^(\d{5})(\d{3}).*/, '$1-$2');
        }
        return value;
    }

    /**
     * Aplica máscara de moeda brasileira
     * @param {string} value - Valor para aplicar máscara
     * @returns {string} - Valor com máscara aplicada
     */
    static currencyMask(value) {
        value = value.replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        value = value.replace(/(\d)(?=(\d{3})+\.)/g, '$1.');
        value = value.replace('.', ',');
        value = value.replace(/(\d+),(\d{2})$/, '$1,$2');
        return value;
    }
}

// Disponibilizar globalmente
window.ValidationUtils = ValidationUtils;
window.MaskUtils = MaskUtils;