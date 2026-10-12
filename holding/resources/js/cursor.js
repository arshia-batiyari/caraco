const openLabel = document.body.dataset.cursorOpen || "";
const viewLabel = document.body.dataset.cursorView || "";
const playLabel = document.body.dataset.cursorPlay || "";
const coarsePointer = window.matchMedia("(pointer: coarse)").matches;

if (!coarsePointer && openLabel) {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const cursor = document.createElement("div");
    cursor.className = "k-cursor";
    cursor.setAttribute("aria-hidden", "true");
    cursor.innerHTML = "<span></span>";
    document.body.appendChild(cursor);
    document.documentElement.classList.add("has-cursor");

    const label = cursor.querySelector("span");
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let frame = 0;

    const place = () => {
        cursor.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translate(-50%, -50%)`;
    };

    const follow = () => {
        currentX += (targetX - currentX) * 0.16;
        currentY += (targetY - currentY) * 0.16;
        place();
        if (Math.abs(targetX - currentX) > 0.4 || Math.abs(targetY - currentY) > 0.4) {
            frame = window.requestAnimationFrame(follow);
        } else {
            frame = 0;
        }
    };

    const poke = () => {
        if (reduceMotion) {
            currentX = targetX;
            currentY = targetY;
            place();
            return;
        }
        if (!frame) {
            frame = window.requestAnimationFrame(follow);
        }
    };

    window.addEventListener("mousemove", (event) => {
        targetX = event.clientX;
        targetY = event.clientY;
        cursor.classList.remove("is-hide");
        poke();
    }, { passive: true });

    document.documentElement.addEventListener("mouseleave", () => {
        cursor.classList.add("is-hide");
    });

    const name = (element, text) => {
        if (!element || element.dataset.cursor) {
            return;
        }
        element.dataset.cursor = text;
    };

    document.querySelectorAll(".k-brand, .k-nav a, .k-header-cta, .k-btn, .k-footer a, .k-more a, .k-locale-toggle, .k-next-link").forEach((element) => {
        name(element, openLabel);
    });
    document.querySelectorAll(".k-row, .k-plate, .k-index-link, .k-portfolio-item, .k-feature a").forEach((element) => {
        name(element, viewLabel);
    });
    document.querySelectorAll(".k-locale-menu a").forEach((element) => {
        name(element, element.textContent.trim());
    });
    document.querySelectorAll(".k-video-poster").forEach((element) => {
        name(element, playLabel);
    });

    const show = (element) => {
        label.textContent = element.dataset.cursor || "";
        cursor.classList.toggle("is-label", label.textContent !== "");
    };

    document.querySelectorAll("[data-cursor]").forEach((element) => {
        element.addEventListener("mouseenter", () => show(element));
        element.addEventListener("mouseleave", () => cursor.classList.remove("is-label"));
    });

    if (!reduceMotion) {
        const clamp = (value, limit) => Math.max(-limit, Math.min(limit, value));
        document.querySelectorAll(".k-header-cta, .k-stage .k-btn, .k-hero .k-btn").forEach((element) => {
            element.addEventListener("mousemove", (event) => {
                const box = element.getBoundingClientRect();
                const dx = clamp((event.clientX - (box.left + box.width / 2)) * 0.28, 12);
                const dy = clamp((event.clientY - (box.top + box.height / 2)) * 0.4, 8);
                element.style.transform = `translate(${dx}px, ${dy}px)`;
            });
            element.addEventListener("mouseleave", () => {
                element.style.transform = "";
            });
        });

        document.querySelectorAll(".k-next").forEach((element) => {
            const arrow = element.querySelector(".k-next-arrow");
            if (!arrow) {
                return;
            }
            element.addEventListener("mousemove", (event) => {
                const box = element.getBoundingClientRect();
                const dx = clamp(((event.clientX - box.left) / box.width - 0.5) * 72, 36);
                const dy = clamp(((event.clientY - box.top) / box.height - 0.5) * 48, 22);
                arrow.style.transform = `translate(${dx}px, ${dy}px)`;
            });
            element.addEventListener("mouseleave", () => {
                arrow.style.transform = "";
            });
        });
    }
}
