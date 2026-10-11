document.querySelectorAll("[data-video]").forEach((root) => {
    const button = root.querySelector("[data-video-play]");
    const template = root.querySelector("template");
    if (!button || !template) {
        return;
    }

    button.addEventListener("click", () => {
        const frame = root.querySelector(".k-video-frame");
        frame?.append(template.content.cloneNode(true));
        button.remove();
    });
});
