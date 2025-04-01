<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { sidebarItems, hasPermission } from "@/menu";

const page = usePage();
const currentHour = ref(new Date().getHours());
const greeting = computed(() => {
    if (currentHour.value >= 5 && currentHour.value < 12) {
        return "Bom dia";
    } else if (currentHour.value >= 12 && currentHour.value < 18) {
        return "Boa tarde";
    } else {
        return "Boa noite";
    }
});

const currentTime = ref("");
let timeInterval = null;

const updateTime = () => {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    currentTime.value = `${hours}:${minutes}:${seconds}`;
    currentHour.value = now.getHours();
};

const formattedDate = computed(() => {
    const now = new Date();
    const weekdays = [
        "Domingo",
        "Segunda-feira",
        "Terça-feira",
        "Quarta-feira",
        "Quinta-feira",
        "Sexta-feira",
        "Sábado",
    ];
    const months = [
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
    ];

    const weekday = weekdays[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();

    return `${weekday}, ${day} de ${month} de ${year}`;
});

const checkPermission = (permission) => hasPermission(permission, page);

const menuCards = computed(() => {
    const cards = [];

    sidebarItems.forEach((section) => {
        const visibleCards = [];

        section.items.forEach((item) => {
            if (
                item.type === "link" &&
                (!item.permission || checkPermission(item.permission))
            ) {
                visibleCards.push({
                    type: "card",
                    icon: item.iconClass,
                    label: item.label,
                    route: item.routeName,
                    isComingSoon: item.label === "Em Breve",
                });
            } else if (item.type === "collapsible") {
                if (item.subItems) {
                    item.subItems.forEach((subItem) => {
                        if (
                            !subItem.permission ||
                            checkPermission(subItem.permission)
                        ) {
                            visibleCards.push({
                                type: "card",
                                icon: subItem.iconClass || item.iconClass,
                                label: subItem.label,
                                route: subItem.routeName,
                                isComingSoon: subItem.label === "Em Breve",
                            });
                        }
                    });
                }
            }
        });

        if (visibleCards.length > 0) {
            cards.push({
                type: "header",
                label: section.label,
            });

            cards.push(...visibleCards);
        }
    });

    return cards;
});

onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
});

onBeforeUnmount(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <div class="d-flex justify-content-between">
            <div>
                <h4>{{ greeting }}, {{ $page.props.auth.user.name }}!</h4>
                <span class="text-muted text-lowercase">{{
                    formattedDate
                }}</span>
            </div>
            <div class="ml-auto text-right">
                <h2>{{ currentTime }}</h2>
            </div>
        </div>

        <div class="row">
            <template v-for="(item, index) in menuCards" :key="index">
                <div v-if="item.type === 'header'" class="col-12">
                    <h4 class="mt-4">{{ item.label }}</h4>
                </div>

                <div
                    v-else-if="item.type === 'card'"
                    class="col-sm-6 col-md-4 col-lg-3 mb-4"
                >
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <i :class="[item.icon, 'fa-2x mr-3']"></i>
                                <h5 class="card-title mb-0">
                                    {{ item.label }}
                                </h5>
                            </div>
                            <button
                                @click="
                                    item.isComingSoon
                                        ? null
                                        : $inertia.visit(route(item.route))
                                "
                                :class="[
                                    'btn btn-primary mt-auto',
                                    {
                                        'opacity-50 cursor-not-allowed':
                                            item.isComingSoon,
                                    },
                                ]"
                                :disabled="item.isComingSoon"
                            >
                                {{ item.isComingSoon ? "Em Breve" : "Acessar" }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
