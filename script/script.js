document.addEventListener("DOMContentLoaded", () => {
  const toggles = document.querySelectorAll(".project");

  toggles.forEach(toggle => {
    toggle.addEventListener("click", () => handleToggle(toggle));
    toggle.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        handleToggle(toggle);
      }
    });
  });

  function handleToggle(element) {
    const targetId = element.getAttribute("data-target");
    const details = document.getElementById(`${targetId}-details`);

    if (!details) {
      console.warn(`Aucun élément trouvé pour ID : ${targetId}-details`);
      return;
    }

    const isExpanded = element.getAttribute("aria-expanded") === "true";
    element.setAttribute("aria-expanded", String(!isExpanded));
    details.hidden = isExpanded;
  }
});


// Ajout mode compact
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("compactToggle");
  if (!btn) return;
  document.body.classList.add("compact"); // par défaut compact
  btn.setAttribute("aria-pressed", "true");
  btn.addEventListener("click", () => {
    const compact = document.body.classList.toggle("compact");
    btn.setAttribute("aria-pressed", compact ? "true" : "false");
  });
});