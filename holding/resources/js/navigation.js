const header = document.querySelector("[data-nav]");

if (header) {
    const toggle = header.querySelector("[data-menu-toggle]");
    const panel = header.querySelector("[data-menu-panel]");
    const inertTargets = document.querySelectorAll("main, .k-footer");

    const setOpen = (open) => {
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
            panel.querySelector("a")?.focus();
        }
    };

    toggle?.addEventListener("click", () => {
        setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && toggle?.getAttribute("aria-expanded") === "true") {
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
}
