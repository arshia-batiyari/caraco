document.querySelectorAll("[data-locale-tabs]").forEach((root) => {
    const tabs = [...root.querySelectorAll("[data-locale-tab]")];
    const panels = [...root.querySelectorAll("[data-locale-panel]")];

    const show = (locale) => {
        tabs.forEach((tab) => {
            tab.setAttribute("aria-selected", tab.getAttribute("data-locale-tab") === locale ? "true" : "false");
        });
        panels.forEach((panel) => {
            panel.hidden = panel.getAttribute("data-locale-panel") !== locale;
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener("click", () => show(tab.getAttribute("data-locale-tab")));
    });

    const invalid = root.querySelector(".is-invalid");
    const panel = invalid?.closest("[data-locale-panel]");
    if (panel) {
        show(panel.getAttribute("data-locale-panel"));
    }
});
