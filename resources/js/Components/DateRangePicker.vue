<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import "daterangepicker/daterangepicker.css";
import "daterangepicker";

const props = defineProps({
    startDate: {
        type: [Date, String],
        default: () => new Date(),
    },
    endDate: {
        type: [Date, String],
        default: () => {
            const date = new Date();
            date.setDate(date.getDate() + 7);
            return date;
        },
    },
    format: {
        type: String,
        default: "DD/MM/YYYY",
    },
    locale: {
        type: Object,
        default: () => ({
            format: "DD/MM/YYYY",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "De",
            toLabel: "Até",
            customRangeLabel: "Personalizado",
            weekLabel: "S",
            daysOfWeek: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
            monthNames: [
                "Janeiro",
                "Fevereiro",
                "Março",
                "Abril",
                "Maio",
                "Junho",
                "Julho",
                "Agosto",
                "Setembro",
                "Outubro",
                "Novembro",
                "Dezembro",
            ],
            firstDay: 0,
        }),
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    placeholder: {
        type: String,
        default: "Selecione um período",
    },
    customClass: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    name: {
        type: String,
        default: "daterange",
    },
    id: {
        type: String,
        default: "date-range-picker",
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "update:startDate",
    "update:endDate",
    "change",
    "apply",
    "cancel",
]);

const inputElement = ref(null);
let daterangepicker = null;

onMounted(() => {
    if (window.jQuery && window.moment) {
        initializePicker();
    } else {
        console.error(
            "DateRangePicker requer jQuery e Moment.js disponíveis globalmente"
        );
    }
});

onBeforeUnmount(() => {
    destroyPicker();
});

watch(
    () => props.startDate,
    (newVal) => {
        if (daterangepicker && newVal) {
            try {
                daterangepicker.setStartDate(window.moment(newVal));
            } catch (e) {
                console.error("Erro ao atualizar startDate:", e);
            }
        }
    }
);

watch(
    () => props.endDate,
    (newVal) => {
        if (daterangepicker && newVal) {
            try {
                daterangepicker.setEndDate(window.moment(newVal));
            } catch (e) {
                console.error("Erro ao atualizar endDate:", e);
            }
        }
    }
);

watch(
    () => props.disabled,
    () => {
        if (daterangepicker) {
            destroyPicker();
            initializePicker();
        }
    }
);

function destroyPicker() {
    try {
        if (daterangepicker && typeof daterangepicker.remove === "function") {
            daterangepicker.remove();
        }
        if (inputElement.value) {
            window.jQuery(inputElement.value).off("apply.daterangepicker");
            window.jQuery(inputElement.value).off("cancel.daterangepicker");
        }
        daterangepicker = null;
    } catch (e) {
        console.warn("Erro ao destruir daterangepicker:", e);
    }
}

function initializePicker() {
    if (!inputElement.value || !window.jQuery || !window.moment) return;

    destroyPicker();

    try {
        const ranges = {
            "Hoje": [window.moment(), window.moment()],
            // "Ontem": [
            //     window.moment().subtract(1, "days"),
            //     window.moment().subtract(1, "days"),
            // ],
            "Últimos 7 Dias": [
                window.moment().subtract(6, "days"),
                window.moment(),
            ],
            // "Últimos 30 dias": [
            //     window.moment().subtract(29, "days"),
            //     window.moment(),
            // ],
            "Mês Passado": [
                window.moment().subtract(1, "month").startOf("month"),
                window.moment().subtract(1, "month").endOf("month"),
            ],
            "Este Mês": [
                window.moment().startOf("month"),
                window.moment().endOf("month"),
            ],
            "Próximo Mês": [
                window.moment().add(1, "month").startOf("month"),
                window.moment().add(1, "month").endOf("month"),
            ],
            "Este Ano": [
                window.moment().startOf("year"),
                window.moment().endOf("year"),
            ],
        };

        const options = {
            startDate: window.moment(props.startDate),
            endDate: window.moment(props.endDate),
            locale: props.locale,
            ranges: ranges,
            autoApply: false,
            alwaysShowCalendars: true,
            showDropdowns: true,
            linkedCalendars: false,
            showWeekNumbers: false,
            showISOWeekNumbers: false,
            showCustomRangeLabel: true,
            showDropdowns: false,
            opens: "right",
            ...props.options,
        };

        window.jQuery(inputElement.value).daterangepicker(options);
        daterangepicker = window
            .jQuery(inputElement.value)
            .data("daterangepicker");

        window
            .jQuery(inputElement.value)
            .on("apply.daterangepicker", function (ev, picker) {
                const startDate = picker.startDate.format("YYYY-MM-DD");
                const endDate = picker.endDate.format("YYYY-MM-DD");

                emit("update:startDate", startDate);
                emit("update:endDate", endDate);
                emit("change", { startDate, endDate });
                emit("apply", { startDate, endDate });
            });

        window
            .jQuery(inputElement.value)
            .on("cancel.daterangepicker", function () {
                emit("cancel");
            });
    } catch (e) {
        console.error("Erro ao inicializar daterangepicker:", e);
    }
}

function getFormattedRange() {
    if (!props.startDate || !props.endDate) return "";

    try {
        if (window.moment) {
            const start = window.moment(props.startDate).format(props.format);
            const end = window.moment(props.endDate).format(props.format);
            return `${start} - ${end}`;
        } else {
            return formatDateRange();
        }
    } catch (e) {
        console.error("Erro ao formatar intervalo:", e);
        return formatDateRange();
    }
}

function formatDateRange() {
    try {
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, "0");
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const year = date.getFullYear();

            if (props.format === "DD/MM/YYYY") {
                return `${day}/${month}/${year}`;
            }
            return `${year}-${month}-${day}`;
        };

        const start = formatDate(props.startDate);
        const end = formatDate(props.endDate);
        return `${start} - ${end}`;
    } catch (e) {
        return "";
    }
}
</script>

<template>
    <div :class="`daterangepicker-wrapper ${customClass}`">
        <label :for="id" class="form-label">
            {{ placeholder }}
            <span v-if="required" class="text-danger">*</span>
        </label>
        <input
            ref="inputElement"
            type="text"
            :id="id"
            :name="name"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :value="getFormattedRange()"
            class="form-control"
            :required="required"
        />
    </div>
</template>
