<script setup>
import { ref, watch, onMounted } from "vue";
import Select2 from "@/Components/Select2.vue";
import DateRangePicker from "@/Components/DateRangePicker.vue";
import InputField from "@/Components/InputField.vue";

const props = defineProps({
    visible: Boolean,
    filters: Object,
    selectedSupplier: Object,
    selectedCreatedBy: Object,
});

const emit = defineEmits(["cancel", "filter", "reset"]);
const isVisible = ref(props.visible);

const form = ref({
    sequential_id: props.filters?.sequential_id || "",
    supplier_id: props.filters?.supplier_id || "",
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
    created_by: props.filters?.created_by || "",
    status: props.filters?.status || "",
});

const initializeModal = () => {
    if (window.$) {
        $("#filterModal").modal({});
        $("#filterModal").on("hidden.bs.modal", () => emit("cancel"));
    }
};

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
        if (value && window.$) {
            setTimeout(() => $("#filterModal").modal("show"), 100);
        }
    }
);

watch(
    () => props.filters,
    (newFilters) => {
        if (newFilters) {
            form.value = {
                sequential_id: newFilters.sequential_id || "",
                supplier_id: newFilters.supplier_id || "",
                start_date: newFilters.start_date || "",
                end_date: newFilters.end_date || "",
                created_by: newFilters.created_by || "",
                status: newFilters.status || "",
            };
        }
    },
    { deep: true }
);

const closeModal = () => {
    if (window.$) $("#filterModal").modal("hide");
    emit("cancel");
};

const applyFilters = () => {
    emit("filter", form.value);
    closeModal();
};

const resetFilters = () => {
    emit("reset");
    closeModal();
};

const handleKeydown = (event) => {
    if (event.key === "Enter") {
        applyFilters();
    }
};

onMounted(() => {
    initializeModal();
});
</script>

<template>
    <div
        class="modal fade"
        id="filterModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="filterModalLabel"
        aria-hidden="true"
        @keydown="handleKeydown"
    >
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">
                        Filtrar Compras
                    </h5>
                    <button
                        type="button"
                        class="close"
                        @click="closeModal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <InputField
                                id="sequential_id"
                                label="Código"
                                v-model="form.sequential_id"
                                type="text"
                                placeholder="Código da Compra"
                            />
                        </div>

                        <div class="col-md-8">
                            <Select2
                                label="Fornecedor"
                                v-model="form.supplier_id"
                                :search-url="route('api.suppliers.search')"
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar Fornecedor"
                                :initial-options="
                                    selectedSupplier ? [selectedSupplier] : []
                                "
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <DateRangePicker
                                    v-model:startDate="form.start_date"
                                    v-model:endDate="form.end_date"
                                    placeholder="Selecione o Período"
                                />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    v-model="form.status"
                                    class="form-control"
                                >
                                    <option value="">Todos</option>
                                    <option value="pending">Pendente</option>
                                    <option value="finalized">
                                        Finalizado
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <Select2
                                label="Criado por"
                                v-model="form.created_by"
                                :search-url="route('api.users.search')"
                                value-key="id"
                                label-key="name"
                                placeholder="Pesquisar Usuário"
                                :initial-options="
                                    selectedCreatedBy ? [selectedCreatedBy] : []
                                "
                            />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="resetFilters"
                    >
                        <i class="fas fa-times"></i>
                        &nbsp; Limpar Filtros
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="applyFilters"
                    >
                        <i class="fas fa-search"></i>
                        &nbsp; Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
