export function formatCurrencyAbbreviated(value) {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined)
        return "R$ 0,00";
    const isNegative = numValue < 0;
    const absValue = Math.abs(numValue);
    let formattedValue;
    if (absValue >= 1000000) {
        formattedValue = `R$ ${(absValue / 1000000)
            .toFixed(2)
            .replace(".", ",")}M`;
    } else if (absValue >= 100000) {
        formattedValue = `R$ ${(absValue / 1000)
            .toFixed(0)
            .replace(".", ",")}K`;
    } else if (absValue >= 10000) {
        formattedValue = `R$ ${(absValue / 1000)
            .toFixed(1)
            .replace(".", ",")}K`;
    } else if (absValue >= 1000) {
        formattedValue = `R$ ${(absValue / 1000)
            .toFixed(2)
            .replace(".", ",")}K`;
    } else {
        formattedValue = `R$ ${absValue.toFixed(2).replace(".", ",")}`;
    }
    return isNegative ? formattedValue.replace("R$", "-R$") : formattedValue;
}

export function formatCurrency(value) {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined)
        return "R$ 0,00";

    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(numValue);
}

export function formatNumber(value, decimals = 0) {
    const numValue = Number(value);
    if (isNaN(numValue) || value === null || value === undefined) return "0";

    return new Intl.NumberFormat("pt-BR", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(numValue);
}

export function formatDateTime(dateString) {
    if (!dateString) return "";

    const [datePart, timePart] = dateString.split("T");

    if (!datePart) return "";

    const [year, month, day] = datePart.split("-");
    const formattedDate = `${day}/${month}/${year}`;

    if (!timePart) return formattedDate;

    const time = timePart.split(".")[0].split("Z")[0];

    return `${formattedDate} ${time}`;
}

export function formatDate(dateString) {
    if (!dateString) return "";

    if (dateString.includes("T")) {
        dateString = dateString.split("T")[0];
    }

    const [year, month, day] = dateString.split("-");

    return `${day}/${month}/${year}`;
}

export function formatIsoDate(input) {
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

    return date.toISOString().split("T")[0];
}

export function formatSequentialId(id) {
    return String(id).padStart(6, "0");
}
