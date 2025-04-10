/**
 * Formata um valor numérico para moeda brasileira (R$) com abreviações para valores grandes
 * @param {number|string} value - Valor a ser formatado
 * @returns {string} - Valor formatado como moeda abreviada
 */
export const formatCurrencyAbbreviated = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined)
        return "R$ 0,00";
    const isNegative = numValue < 0;
    const absValue = Math.abs(numValue);
    let formattedValue;
    if (absValue >= 1000000) {
        formattedValue = `R$ ${(absValue / 1000000).toFixed(2).replace(".", ",")}M`;
    } else if (absValue >= 100000) {
        formattedValue = `R$ ${(absValue / 1000).toFixed(0).replace(".", ",")}K`;
    } else if (absValue >= 10000) {
        formattedValue = `R$ ${(absValue / 1000).toFixed(1).replace(".", ",")}K`;
    } else if (absValue >= 1000) {
        formattedValue = `R$ ${(absValue / 1000).toFixed(2).replace(".", ",")}K`;
    } else {
        formattedValue = `R$ ${absValue.toFixed(2).replace(".", ",")}`;
    }
    return isNegative ? formattedValue.replace("R$", "-R$") : formattedValue;
};

/**
 * Formata um valor numérico para moeda brasileira (R$) usando o formato padrão
 * @param {number|string} value - Valor a ser formatado
 * @returns {string} - Valor formatado como moeda
 */
export const formatCurrency = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined)
        return "R$ 0,00";

    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(numValue);
};

/**
 * Formata um valor numérico com separadores de milhares
 * @param {number|string} value - Valor a ser formatado
 * @param {number} decimals - Número de casas decimais (padrão: 0)
 * @returns {string} - Valor formatado com separadores
 */
export const formatNumber = (value, decimals = 0) => {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined) return "0";

    return new Intl.NumberFormat("pt-BR", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(numValue);
};

/**
 * Formata uma string de data e hora no padrão brasileiro (DD/MM/YYYY HH:MM:SS)
 * @param {string} dateString - String de data e hora (formato ISO "YYYY-MM-DDThh:mm:ss...")
 * @returns {string} - Data e hora formatada como DD/MM/YYYY HH:MM:SS
 */
export const formatDateTime = (dateString) => {
    if (!dateString) return "";

    const [datePart, timePart] = dateString.split('T');

    if (!datePart) return "";

    const [year, month, day] = datePart.split("-");
    const formattedDate = `${day}/${month}/${year}`;

    if (!timePart) return formattedDate;

    const time = timePart.split('.')[0].split('Z')[0];

    return `${formattedDate} ${time}`;
};

/**
 * Formata uma data no padrão brasileiro (DD/MM/YYYY)
 * @param {string} dateString - String de data (formato ISO "YYYY-MM-DD" ou "YYYY-MM-DDT...")
 * @returns {string} - Data formatada como DD/MM/YYYY
 */
export const formatDate = (dateString) => {
    if (!dateString) return "";

    if (dateString.includes("T")) {
        dateString = dateString.split("T")[0];
    }

    const [year, month, day] = dateString.split("-");

    return `${day}/${month}/${year}`;
};

/**
 * Formata uma data no padrão brasileiro (DD/MM/YYYY) com hora
 * @param {string} dateString - String de data (formato ISO "YYYY-MM-DD" ou "YYYY-MM-DDT...")
 * @returns {string} - Data formatada como DD/MM/YYYY
 */
export const formatIsoDate = (input) => {
    if (!input) return "";

    let date;

    if (input instanceof Date) {
        date = input;
    } else if (typeof input === "string") {
        if (input.includes("T")) {
            input = input.split("T")[0];
        }
        const [year, month, day] = input.split("-");
        date = new Date(year, month - 1, day);
    } else {
        return "";
    }

    // Retorna em formato ISO YYYY-MM-DD
    return date.toISOString().split("T")[0];
};

/**
 * Formata uma data no padrão brasileiro (DD/MM/YYYY) com hora
 * @param {string} dateString - String de data (formato ISO "YYYY-MM-DD" ou "YYYY-MM-DDT...")
 * @returns {string} - Data formatada como DD/MM/YYYY
 */
export const formatSequentialId = (id) => {
    return String(id).padStart(6, "0");
};
