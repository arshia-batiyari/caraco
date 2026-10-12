const nodes = document.querySelectorAll(".bx-rise");

if (!nodes.length) {
    // Nothing on this page uses the entrance motion.
} else if (!document.documentElement.classList.contains("bx-motion") || !("IntersectionObserver" in window)) {
    nodes.forEach((node) => node.classList.add("is-in"));
} else {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            entry.target.classList.add("is-in");
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.18 });

    nodes.forEach((node) => observer.observe(node));
}
