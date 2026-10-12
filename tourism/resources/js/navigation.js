const header = document.querySelector("[data-nav]");

if (header) {
    const toggle = header.querySelector("[data-menu-toggle]");
    const panel = header.querySelector("[data-menu-panel]");
    const inertTargets = document.querySelectorAll("main, .k-footer");
    const locale = header.querySelector("[data-locale]");
    const localeButton = locale?.querySelector("[data-locale-toggle]");
    const localeMenu = locale?.querySelector("[data-locale-menu]");

    const setOpen = (open) => {
        if (!panel || !toggle) {
            return;
        }

        panel.classList.toggle("is-open", open);
        header.classList.toggle("is-menu-open", open);
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        document.body.style.overflow = open ? "hidden" : "";
        inertTargets.forEach((element) => {
            if (open) {
                element.setAttribute("inert", "");
            } else {
                element.removeAttribute("inert");
            }
        });
        if (open) {
            panel.querySelector("a, button")?.focus();
        }
    };

    const localeLinks = () => (localeMenu ? [...localeMenu.querySelectorAll("a")] : []);

    const setLocale = (open, focusFirst = false) => {
        if (!localeButton || !localeMenu) {
            return;
        }

        localeButton.setAttribute("aria-expanded", open ? "true" : "false");
        localeMenu.hidden = !open;
        if (open && focusFirst) {
            localeLinks()[0]?.focus();
        }
    };

    toggle?.addEventListener("click", () => {
        setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });

    localeButton?.addEventListener("click", () => {
        setLocale(localeButton.getAttribute("aria-expanded") !== "true");
    });

    localeButton?.addEventListener("keydown", (event) => {
        if (event.key === "ArrowDown") {
            event.preventDefault();
            setLocale(true, true);
        }
    });

    localeMenu?.addEventListener("keydown", (event) => {
        const items = localeLinks();
        const index = items.indexOf(document.activeElement);

        if (event.key === "ArrowDown" || event.key === "ArrowUp") {
            event.preventDefault();
            const next = event.key === "ArrowDown" ? index + 1 : index - 1;
            items[(next + items.length) % items.length]?.focus();
        }

        if (event.key === "Escape") {
            event.stopPropagation();
            setLocale(false);
            localeButton?.focus();
        }
    });

    document.addEventListener("click", (event) => {
        if (locale && !locale.contains(event.target)) {
            setLocale(false);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        if (localeButton?.getAttribute("aria-expanded") === "true") {
            setLocale(false);
            localeButton.focus();
            return;
        }

        if (toggle?.getAttribute("aria-expanded") === "true") {
            setOpen(false);
            toggle.focus();
        }
    });

    panel?.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", () => {
            if (window.matchMedia("(max-width: 959px)").matches) {
                setOpen(false);
            }
        });
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth >= 960 && toggle?.getAttribute("aria-expanded") === "true") {
            setOpen(false);
        }
    });

    const markStuck = () => {
        header.classList.toggle("is-stuck", window.scrollY > 12);
    };

    markStuck();
    window.addEventListener("scroll", markStuck, { passive: true });
}
