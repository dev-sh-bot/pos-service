/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

import "./bootstrap";
import { CommonHelper } from "./helpers/commonHelper";

window.CommonHelper = CommonHelper;

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import "./components/Cart";

import "./components/Purchase";

const themeStorageKey = "snd-theme";
const themeRoot = document.documentElement;
const systemThemeQuery = window.matchMedia?.("(prefers-color-scheme: dark)");
const validTheme = (theme) => theme === "light" || theme === "dark";
const readStoredTheme = () => {
    try {
        const theme = window.localStorage.getItem(themeStorageKey);
        return validTheme(theme) ? theme : null;
    } catch (error) {
        return null;
    }
};
let userHasThemeChoice = Boolean(readStoredTheme());

const updateThemeButtons = (theme) => {
    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        const label = theme === "dark" ? button.dataset.labelLight : button.dataset.labelDark;
        button.setAttribute("aria-label", label || "Toggle color theme");
        button.setAttribute("title", label || "Toggle color theme");
        button.setAttribute("aria-pressed", String(theme === "dark"));
    });
};

const applyTheme = (theme, preference = "user") => {
    const nextTheme = validTheme(theme) ? theme : "light";
    themeRoot.setAttribute("data-theme", nextTheme);
    themeRoot.setAttribute("data-theme-preference", preference);
    themeRoot.style.colorScheme = nextTheme;
    updateThemeButtons(nextTheme);
    document.dispatchEvent(new CustomEvent("snd:themechange", { detail: { theme: nextTheme } }));
};

applyTheme(themeRoot.dataset.theme || (systemThemeQuery?.matches ? "dark" : "light"), userHasThemeChoice ? "user" : "system");

document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
    button.addEventListener("click", () => {
        const nextTheme = themeRoot.dataset.theme === "dark" ? "light" : "dark";
        userHasThemeChoice = true;
        try {
            window.localStorage.setItem(themeStorageKey, nextTheme);
        } catch (error) {
            // Keep the current page usable when browser storage is unavailable.
        }
        applyTheme(nextTheme, "user");
    });
});

if (systemThemeQuery) {
    const followSystemTheme = (event) => {
        if (!userHasThemeChoice) applyTheme(event.matches ? "dark" : "light", "system");
    };
    if (systemThemeQuery.addEventListener) systemThemeQuery.addEventListener("change", followSystemTheme);
    else systemThemeQuery.addListener(followSystemTheme);
}

window.addEventListener("storage", (event) => {
    if (event.key !== themeStorageKey && event.key !== null) return;

    const storedTheme = event.key === null ? null : validTheme(event.newValue) ? event.newValue : readStoredTheme();
    userHasThemeChoice = Boolean(storedTheme);
    applyTheme(storedTheme || (systemThemeQuery?.matches ? "dark" : "light"), userHasThemeChoice ? "user" : "system");
});

// Workspace navigation retains the existing page and permission hooks.
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-profile-avatar]").forEach((image) => {
        const showNameFallback = () => {
            image.hidden = true;
            const fallback = image.closest(".snd-profile-dropdown")?.querySelector("[data-profile-initial-fallback]");
            if (fallback) fallback.hidden = false;
            image.closest(".snd-profile-dropdown")?.classList.add("has-avatar-fallback");
        };
        image.addEventListener("error", showNameFallback, { once: true });
        if (image.complete && image.naturalWidth === 0) showNameFallback();
    });

    // Below 1024px the navigation is a keyboard-accessible drawer.
    const isMobile = () => window.matchMedia("(max-width: 1023.98px)").matches;

    document.querySelectorAll(".nav-sidebar .nav-item > .nav-link").forEach((link) => {
        const parent = link.closest(".nav-item");
        const treeview = parent ? parent.querySelector(":scope > .nav-treeview") : null;
        if (!treeview) return;

        link.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const isOpen = parent.classList.contains("menu-open");
            document.querySelectorAll(".nav-sidebar .nav-item.menu-open").forEach((openItem) => {
                if (openItem !== parent) openItem.classList.remove("menu-open");
            });
            parent.classList.toggle("menu-open", !isOpen);
            link.setAttribute("aria-expanded", String(!isOpen));
            document.querySelectorAll(".nav-sidebar .nav-item:not(.menu-open) > .nav-link[aria-expanded]").forEach((closedLink) => closedLink.setAttribute("aria-expanded", "false"));
        });
    });

    const sidebarToggles = [...document.querySelectorAll("[data-sidebar-toggle]")];
    const sidebar = document.getElementById("pos-sidebar");
    const syncSidebarToggleState = () => {
        if (sidebar) {
            const drawerClosed = isMobile() && !document.body.classList.contains("snd-sidebar-open");
            sidebar.inert = drawerClosed;
            sidebar.toggleAttribute("aria-hidden", drawerClosed);
            if (drawerClosed) sidebar.setAttribute("aria-hidden", "true");
        }
        if (!sidebarToggles.length) return;

        const expanded = isMobile()
            ? document.body.classList.contains("snd-sidebar-open")
            : !document.body.classList.contains("sidebar-collapse");
        sidebarToggles.forEach((toggle) => toggle.setAttribute("aria-expanded", String(expanded)));
    };
    const closeMobileSidebar = () => {
        const wasOpen = document.body.classList.contains("snd-sidebar-open");
        document.body.classList.remove("snd-sidebar-open");
        syncSidebarToggleState();
        if (wasOpen && isMobile()) sidebarToggles[0]?.focus();
    };

    // Keep the responsive shell toggle separate from AdminLTE's PushMenu hook.
    sidebarToggles.forEach((sidebarToggle) => sidebarToggle.addEventListener("click", (event) => {
        event.preventDefault();
        if (isMobile()) {
            document.body.classList.toggle("snd-sidebar-open");
        } else {
            document.body.classList.toggle("sidebar-collapse");
        }
        syncSidebarToggleState();
        if (isMobile() && document.body.classList.contains("snd-sidebar-open")) {
            sidebar?.querySelector("a[href]")?.focus();
        }
    }));

    document.addEventListener("click", (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;

        if (target.closest("[data-sidebar-close], [data-sidebar-overlay]")) {
            closeMobileSidebar();
            return;
        }

        if (isMobile() && target.closest(".snd-nav-list .nav-link[href]:not([href='#'])")) {
            closeMobileSidebar();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && isMobile()) closeMobileSidebar();
        if (event.key === "Tab" && isMobile() && document.body.classList.contains("snd-sidebar-open") && sidebar) {
            const targets = [...sidebar.querySelectorAll("a[href], button:not([disabled])")].filter((element) => element.getClientRects().length);
            const first = targets[0];
            const last = targets[targets.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
    });

    window.addEventListener("resize", () => {
        if (isMobile()) {
            syncSidebarToggleState();
        } else {
            closeMobileSidebar();
        }
    });
    syncSidebarToggleState();
});
