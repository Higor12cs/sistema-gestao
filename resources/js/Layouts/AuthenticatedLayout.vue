<script setup>
import { ref, computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";
import NavItem from "@/Components/NavItem.vue";
import NavItemCollapsible from "@/Components/NavItemCollapsible.vue";
import { sidebarItems, hasPermission } from "@/menu";
import { toast } from "vue3-toastify";

const appName = import.meta.env.VITE_APP_NAME || "";
const currentTime = ref("");
const page = usePage();
const darkMode = ref(false);

// Função para exibir mensagens flash como toasts
const showFlashMessages = () => {
    const props = usePage().props;

    // Verifica se props.flash existe antes de tentar acessá-lo
    if (props.flash) {
        // Mensagem de sucesso
        if (props.flash.success) {
            toast.success(props.flash.success);
        }

        // Mensagem de erro
        if (props.flash.error) {
            toast.error(props.flash.error);
        }

        // Mensagem de informação
        if (props.flash.info) {
            toast.info(props.flash.info);
        }

        // Mensagem de alerta
        if (props.flash.warning) {
            toast.warning(props.flash.warning);
        }
    }

    // Verifica mensagens individuais no nível raiz das props também
    // (algumas versões do Laravel/Inertia podem estruturar as mensagens assim)
    if (props.success) {
        toast.success(props.success);
    }

    if (props.error) {
        toast.error(props.error);
    }

    if (props.info) {
        toast.info(props.info);
    }

    if (props.warning) {
        toast.warning(props.warning);
    }
};

// Verificar mensagens flash ao inicializar o componente
onMounted(() => {
    showFlashMessages();
});

// Verificar mensagens flash quando as props mudarem
watch(
    () => page.props,
    (newProps) => {
        showFlashMessages();
    },
    { deep: true }
);

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    document.body.classList.toggle("dark-mode");
    localStorage.setItem("darkMode", darkMode.value ? "true" : "false");
};

const checkPermission = (permission) => hasPermission(permission, page);

const visibleMenuItems = computed(() => {
    return sidebarItems.filter((section) => {
        const visibleItems = section.items.filter((item) => {
            if (item.type === "link") {
                return !item.permission || checkPermission(item.permission);
            } else if (item.type === "collapsible") {
                const filteredSubItems = item.subItems.filter(
                    (subItem) =>
                        !subItem.permission ||
                        checkPermission(subItem.permission)
                );

                if (filteredSubItems.length === 0) return false;

                item.filteredSubItems = filteredSubItems;
                return !item.permission || checkPermission(item.permission);
            }
            return false;
        });

        section.visibleItems = visibleItems;
        return visibleItems.length > 0;
    });
});

const updateTime = () => {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    currentTime.value = `${hours}:${minutes}:${seconds}`;
};

onMounted(() => {
    updateTime();
    setInterval(updateTime, 1000);

    // Carregar preferência de modo escuro do localStorage
    const savedDarkMode = localStorage.getItem("darkMode");
    if (savedDarkMode === "true") {
        darkMode.value = true;
        document.body.classList.add("dark-mode");
    }
});
</script>

<template>
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a
                        class="nav-link"
                        data-widget="pushmenu"
                        href="#"
                        role="button"
                    >
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    {{ currentTime }}
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="#"
                        @click.prevent="toggleDarkMode"
                        role="button"
                    >
                        <i
                            class="fas"
                            :class="darkMode ? 'fa-sun' : 'fa-moon'"
                        ></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        {{ $page.props.auth.user.name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="dropdown-item"
                        >
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Sair
                        </Link>
                    </div>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-2">
            <Link href="/" class="brand-link d-flex justify-content-center">
                <span class="brand-text font-weight-semibold">
                    {{ appName }}
                </span>
            </Link>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul
                        class="nav nav-pills nav-sidebar flex-column"
                        data-widget="treeview"
                        role="menu"
                        data-accordion="false"
                    >
                        <template
                            v-for="(section, sectionIndex) in visibleMenuItems"
                            :key="sectionIndex"
                        >
                            <!-- Header -->
                            <li class="nav-header">{{ section.label }}</li>

                            <!-- Menu Items -->
                            <template
                                v-for="(
                                    item, itemIndex
                                ) in section.visibleItems"
                                :key="`${sectionIndex}-${itemIndex}`"
                            >
                                <!-- Regular Link -->
                                <NavItem
                                    v-if="item.type === 'link'"
                                    :route-name="item.routeName"
                                    :icon-class="item.iconClass"
                                    :label="item.label"
                                />

                                <!-- Collapsible Menu -->
                                <NavItemCollapsible
                                    v-else-if="item.type === 'collapsible'"
                                    :icon-class="item.iconClass"
                                    :label="item.label"
                                    :sub-items="item.filteredSubItems"
                                />
                            </template>
                        </template>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid py-3">
                    <slot />
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                <strong>{{ appName }}</strong> 0.0.1
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
</template>
