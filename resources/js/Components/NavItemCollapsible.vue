<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";

const props = defineProps({
    iconClass: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    subItems: {
        type: Array,
        required: true,
    },
    initiallyOpen: {
        type: Boolean,
        default: false,
    },
});

const isOpen = ref(props.initiallyOpen);
const menuElement = ref(null);
const activeSubMenus = ref([]);

const hasAnyActiveRoute = computed(() => {
    const checkForActive = (items) => {
        for (const item of items) {
            if (item.routeName && route().current(item.routeName)) {
                return true;
            }

            if (item.subItems && checkForActive(item.subItems)) {
                return true;
            }
        }
        return false;
    };

    return checkForActive(props.subItems);
});

onMounted(() => {
    props.subItems.forEach((item, index) => {
        if (item.subItems) {
            const hasActiveChild = item.subItems.some(
                (subItem) =>
                    subItem.routeName && route().current(subItem.routeName)
            );

            if (hasActiveChild) {
                activeSubMenus.value.push(index);
                isOpen.value = true;
            }
        } else if (item.routeName && route().current(item.routeName)) {
            isOpen.value = true;
        }
    });

    if (isOpen.value || props.initiallyOpen) {
        if (menuElement.value) {
            menuElement.value.classList.add("menu-open");
        }
    }
});
</script>

<template>
    <li ref="menuElement" class="nav-item has-treeview">
        <a href="#" class="nav-link" :class="{ active: hasAnyActiveRoute }">
            <i :class="`nav-icon ${iconClass}`"></i>
            <p class="ml-1">
                {{ label }}
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <template v-for="(item, index) in subItems" :key="index">
                <li v-if="!item.subItems" class="nav-item">
                    <Link
                        :href="route(item.routeName)"
                        :class="
                            route().current(item.routeName)
                                ? 'nav-link active'
                                : 'nav-link'
                        "
                        preserve-scroll
                    >
                        <i :class="`nav-icon ${item.iconClass}`"></i>
                        <p class="ml-1">
                            {{ item.label }}
                            <span
                                v-if="item.badge"
                                class="badge badge-primary right"
                            >
                                {{ item.badge }}
                            </span>
                        </p>
                    </Link>
                </li>

                <li
                    v-else
                    class="nav-item has-treeview"
                    :class="{ 'menu-open': activeSubMenus.includes(index) }"
                >
                    <a
                        href="#"
                        class="nav-link"
                        :class="{
                            active:
                                item.subItems &&
                                item.subItems.some(
                                    (subItem) =>
                                        subItem.routeName &&
                                        route().current(subItem.routeName)
                                ),
                        }"
                    >
                        <i :class="`nav-icon ${item.iconClass}`"></i>
                        <p class="ml-1">
                            {{ item.label }}
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li
                            v-for="(subItem, subIndex) in item.subItems"
                            :key="subIndex"
                            class="nav-item"
                        >
                            <Link
                                :href="route(subItem.routeName)"
                                :class="
                                    route().current(subItem.routeName)
                                        ? 'nav-link active'
                                        : 'nav-link'
                                "
                            >
                                <i :class="`nav-icon ${subItem.iconClass}`"></i>
                                <p class="ml-1">
                                    {{ subItem.label }}
                                    <span
                                        v-if="subItem.badge"
                                        class="badge badge-primary right"
                                    >
                                        {{ subItem.badge }}
                                    </span>
                                </p>
                            </Link>
                        </li>
                    </ul>
                </li>
            </template>
        </ul>
    </li>
</template>
