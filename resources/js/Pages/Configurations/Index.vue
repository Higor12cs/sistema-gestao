<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

const props = defineProps({
    configurations: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Configurações" />
    <AppLayout>
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Configurações</h4>
                <Breadcrumb
                    :breadcrumb="[
                        { label: 'Home', routeName: 'home.index' },
                        { label: 'Configurações' },
                    ]"
                />
            </div>
        </div>

        <div class="card">
            <div class="card-header">Configurações</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-bordered table-stirped table-hover"
                    >
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Configuração</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="configuration in configurations"
                                :key="configuration.id"
                            >
                                <td>
                                    {{
                                        String(configuration.id).padStart(
                                            6,
                                            "0",
                                        )
                                    }}
                                </td>
                                <td>{{ configuration.description }}</td>
                                <td>{{ configuration.value }}</td>
                                <td>
                                    <Link
                                        :href="
                                            route('configurations.edit', {
                                                configuration: configuration.id,
                                            })
                                        "
                                        class="btn btn-sm btn-secondary"
                                    >
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
