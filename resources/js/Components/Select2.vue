<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import Select2 from "select2";
import "admin-lte/plugins/select2/css/select2.min.css";
import "admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css";

Select2($);

const props = defineProps({
    modelValue: {
        type: [String, Number, Array, Object],
        default: null,
    },
    label: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Pesquisar",
    },
    error: {
        type: String,
        default: "",
    },
    required: {
        type: Boolean,
        default: false,
    },
    searchUrl: {
        type: String,
        required: true,
    },
    valueKey: {
        type: String,
        default: "id",
    },
    labelKey: {
        type: String,
        default: "name",
    },
    multiple: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);
const selectElement = ref(null);
const selectId = `select2-${Math.random().toString(36).substr(2, 9)}`;
const isLoading = ref(!!props.modelValue);
const $select = ref(null);

onMounted(() => {
    $select.value = $(selectElement.value);

    $select.value.select2({
        placeholder: props.placeholder,
        allowClear: true,
        width: "100%",
        theme: "bootstrap4",
        language: {
            inputTooShort: function () {
                return "Digite mais caracteres...";
            },
            noResults: function () {
                return "Nenhum resultado encontrado.";
            },
            searching: function () {
                return "Buscando...";
            },
            loadingMore: function () {
                return "Carregando mais resultados...";
            },
        },
        ajax: {
            url: props.searchUrl,
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    search: params.term || "",
                    page: params.page || 1,
                };
            },
            processResults: function (data, params) {
                const items = Array.isArray(data) ? data : data.data || [];
                return {
                    results: items.map((item) => ({
                        id: item[props.valueKey],
                        text: item[props.labelKey],
                        data: item,
                    })),
                };
            },
            cache: true,
        },
        minimumInputLength: 0,
    });

    $select.value.on("change", function () {
        const value = $select.value.val();
        emit("update:modelValue", value);
        emit("change", value);
    });

    $select.value.on("focus", function () {
        $select.value.select2("open");
    });

    if (props.modelValue) {
        fetch(`${props.searchUrl}?ids=${props.modelValue}`)
            .then((response) => response.json())
            .then((data) => {
                const items = Array.isArray(data) ? data : data.data || [];
                if (items.length > 0) {
                    const item = items[0];
                    const option = new Option(
                        item[props.labelKey],
                        item[props.valueKey],
                        true,
                        true
                    );
                    $select.value.append(option).trigger("change");
                }
                isLoading.value = false;
            })
            .catch(() => {
                isLoading.value = false;
            });
    }
});

watch(
    () => props.error,
    (newError) => {
        if ($select.value) {
            const container = $select.value.next(".select2-container");
            if (newError) {
                container.addClass("select2-container--error");
            } else {
                container.removeClass("select2-container--error");
            }
        }
    }
);

watch(
    () => props.modelValue,
    (newVal) => {
        if (!newVal && $select.value) {
            $select.value.val(null).trigger("change");
        }
    }
);

onBeforeUnmount(() => {
    if ($select.value) {
        $select.value.select2("destroy");
    }
});
</script>

<template>
    <div class="form-group">
        <label v-if="label" :for="selectId" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <select
            :id="selectId"
            ref="selectElement"
            class="form-control"
            :required="required"
            :disabled="isLoading"
        ></select>

        <div v-if="error" class="invalid-feedback d-block">
            {{ error }}
        </div>
    </div>
</template>

<style scoped>
:deep(.select2-container--error) .select2-selection {
    border-color: #dc3545 !important;
}

.invalid-feedback.d-block {
    display: block !important;
}
</style>
