<script setup>
import { ref, onMounted, watch } from "vue";
import Inputmask from "../../../node_modules/inputmask/dist/inputmask.es6.js";

const props = defineProps({
    modelValue: {
        type: [String, Number, Date],
        default: "",
    },
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: "",
    },
    type: {
        type: String,
        default: "text",
    },
    placeholder: {
        type: String,
        default: "",
    },
    required: {
        type: Boolean,
        default: false,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: "",
    },
    // New property for mask type
    maskType: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);
const inputElement = ref(null);
const inputMask = ref(null);

onMounted(() => {
    if (props.maskType && inputElement.value) {
        applyMask();
    }
});

watch(
    () => props.maskType,
    () => {
        if (inputElement.value) {
            applyMask();
        }
    }
);

const applyMask = () => {
    if (inputMask.value) {
        inputMask.value.remove();
    }

    switch (props.maskType) {
        case "currency":
            inputMask.value = new Inputmask("currency", {
                prefix: "R$ ",
                groupSeparator: ".",
                alias: "numeric",
                placeholder: "0",
                autoGroup: true,
                digits: 2,
                digitsOptional: false,
                clearMaskOnLostFocus: false,
                radixPoint: ",",
                autoUnmask: true,
                removeMaskOnSubmit: true,
                rightAlign: false,
                onBeforeMask: function (value) {
                    return value ? value.toString().replace(".", ",") : "0";
                },
            });
            break;
        case "cpf":
            inputMask.value = new Inputmask("999.999.999-99");
            break;
        case "cnpj":
            inputMask.value = new Inputmask("99.999.999/9999-99");
            break;
        case "cpf-cnpj":
            inputMask.value = new Inputmask({
                mask: ["999.999.999-99", "99.999.999/9999-99"],
                keepStatic: true,
            });
            break;
        case "phone":
            inputMask.value = new Inputmask({
                mask: ["(99) 9999-9999", "(99) 99999-9999"],
                keepStatic: true,
            });
            break;
        case "cep":
            inputMask.value = new Inputmask("99999-999");
            break;
        case "date":
            inputMask.value = new Inputmask("99/99/9999");
            break;
    }

    if (inputMask.value) {
        inputMask.value.mask(inputElement.value);
    }
};

const handleInput = (event) => {
    let value = event.target.value;

    if (props.maskType === "currency" && inputMask.value) {
        if (inputMask.value.unmaskedvalue) {
            value = inputMask.value.unmaskedvalue(value);
        }
    }

    emit("update:modelValue", value);
};
</script>

<template>
    <div class="form-group">
        <label :for="id" v-if="label">
            {{ label }} <span v-if="required" class="text-danger">*</span>
        </label>

        <input
            ref="inputElement"
            :type="type"
            class="form-control"
            :class="{ 'is-invalid': error }"
            :id="id"
            :value="modelValue"
            @input="handleInput"
            :placeholder="placeholder"
            :required="required"
            :autofocus="autofocus"
            :autocomplete="autocomplete ? 'on' : 'off'"
        />

        <div v-if="error" class="invalid-feedback">
            {{ error }}
        </div>
    </div>
</template>
