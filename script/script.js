/* =========================================================
   JS Global — htdocs/script/script.js
   - Toggle des sections projet (clic + clavier, ARIA)
   - Mode compact (densité réduite)
   - Scanner de fichier JS → liste des fonctions (+ descriptions JSDoc)
   - Auto-init de la section “⚡ script” sur htdocs/htdocs/index.php
   ========================================================= */

'use strict';

/* =========================================================
   TOGGLE DES PROJETS (ouvert/fermé) — accessibilité incluse
   Cible HTML : .project[data-target="slug"] + #slug-details[hidden]
   Exemple :
     <div class="project" data-target="monitor" tabindex="0"
          aria-expanded="false" aria-controls="monitor-details">
       ...
       <div id="monitor-details" hidden>...</div>
     </div>
   ========================================================= */

/**
 * @name initProjectToggles
 * @group Projets / UI
 * @description Active le dépliage/repliage des cartes .project :
 *  - clic souris et clavier (Enter/Espace)
 *  - met à jour aria-expanded, bascule l’attribut `hidden` sur #*-details
 */
document.addEventListener("DOMContentLoaded", initProjectToggles);
function initProjectToggles() {
  const toggles = document.querySelectorAll(".project");
  toggles.forEach(toggle => {
    // Clic
    toggle.addEventListener("click", () => handleToggle(toggle));
    // Clavier : Enter / Espace
    toggle.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        handleToggle(toggle);
      }
    });
  });
}

/**
 * @name handleToggle
 * @group Projets / UI
 * @description Ouvre/ferme les détails d’un projet :
 *  - récupère data-target
 *  - cible #<target>-details
 *  - inverse aria-expanded + hidden
 * @param {HTMLElement} element - Élément .project cliqué/focus
 */
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


/* =========================================================
   MODE COMPACT — densité réduite globale
   Cible HTML : <button id="compactToggle">...</button>
   Effet CSS : ajoute/retire body.compact (voir style.css)
   ========================================================= */

/**
 * @name initCompactMode
 * @group Layout
 * @description Active le mode “compact” par défaut, et permet de le
 *  basculer via le bouton #compactToggle (aria-pressed mis à jour).
 */
document.addEventListener("DOMContentLoaded", initCompactMode);
function initCompactMode() {
  const btn = document.getElementById("compactToggle");
  if (!btn) return;

  // Activé par défaut
  document.body.classList.add("compact");
  btn.setAttribute("aria-pressed", "true");

  // Toggle au clic
  btn.addEventListener("click", () => {
    const compact = document.body.classList.toggle("compact");
    btn.setAttribute("aria-pressed", compact ? "true" : "false");
  });
}


/* =========================================================
   ANALYSEUR DE FONCTIONS JS (+ descriptions JSDoc)
   Usage : listJsFunctionsWithDocs('/script/mon_fichier.js').then(...)
   Retour : [{ name, sig, kind, doc }]
   - kind ∈ "function" | "export" | "arrow" | "func-expr"
   - doc = @description du JSDoc (ou 1ʳᵉ ligne du bloc JSDoc)
   ========================================================= */

/**
 * Parse un bloc JSDoc (/** ... * /) et retourne une description.
 * Priorité à @description, sinon 1ère ligne non tag.
 */
function __parseJsDocBlock(block) {
  const text = block
    .replace(/^\s*\/\*\*|\*\/\s*$/g, '')
    .replace(/^\s*\*\s?/gm, '')
    .trim();

  let desc = '';
  const mDesc = text.match(/@description\s+([^\n]+)/i);
  if (mDesc) desc = mDesc[1].trim();
  if (!desc) {
    const first = text.split('\n').find(l => !l.trim().startsWith('@'));
    if (first) desc = first.trim();
  }
  return desc;
}

/**
 * Analyse une chaîne JS et renvoie les fonctions + doc JSDoc juste au-dessus.
 * Gère:
 *  - /** ... * / export function foo(a,b)
 *  - /** ... * / function foo(a,b)
 *  - /** ... * / const foo = (a,b) => ...
 *  - /** ... * / const foo = function(a,b) ...
 */
function parseFunctionsWithDocsFromSource(source) {
  const out = [];

  // export function / function
  const reFn = /\/\*\*([\s\S]*?)\*\/\s*(export\s+)?(async\s+)?function\s+([A-Za-z_]\w*)\s*\(([^)]*)\)/g;
  // const foo = (...) => ...
  const reArrow = /\/\*\*([\s\S]*?)\*\/\s*const\s+([A-Za-z_]\w*)\s*=\s*(async\s*)?\(([^)]*)\)\s*=>/g;
  // const foo = function(...) ...
  const reExpr = /\/\*\*([\s\S]*?)\*\/\s*const\s+([A-Za-z_]\w*)\s*=\s*(async\s*)?function\s*\(([^)]*)\)/g;

  let m;
  while ((m = reFn.exec(source))) {
    out.push({
      name: m[4],
      sig: `(${(m[5] || '').trim()})`,
      kind: m[2] ? 'export' : 'function',
      doc: __parseJsDocBlock(m[1])
    });
  }
  while ((m = reArrow.exec(source))) {
    out.push({
      name: m[2],
      sig: `(${(m[4] || '').trim()})`,
      kind: 'arrow',
      doc: __parseJsDocBlock(m[1])
    });
  }
  while ((m = reExpr.exec(source))) {
    out.push({
      name: m[2],
      sig: `(${(m[4] || '').trim()})`,
      kind: 'func-expr',
      doc: __parseJsDocBlock(m[1])
    });
  }

  // Fallback: fonctions sans JSDoc (on ne double pas si déjà captées)
  source.replace(/\bfunction\s+([A-Za-z_]\w*)\s*\(([^)]*)\)/g, (_, name, args) => {
    const key = `${name}|(${(args||'').trim()})`;
    if (!out.some(f => `${f.name}|${f.sig}` === key)) {
      out.push({ name, sig: `(${(args||'').trim()})`, kind: 'function', doc: '' });
    }
  });

  // Dédoublonnage et tri
  const uniq = {};
  out.forEach(f => { uniq[`${f.name}|${f.sig}`] = f; });
  return Object.values(uniq).sort((a,b)=> (a.name+a.sig).localeCompare(b.name+b.sig));
}

/**
 * Charge un fichier JS (même origine) et retourne la liste des fonctions + doc.
 * @param {string} url
 * @returns {Promise<Array<{name:string,sig:string,kind:string,doc:string}>>}
 */
async function listJsFunctionsWithDocs(url) {
  const res = await fetch(url, { cache: 'no-store' });
  if (!res.ok) throw new Error(`Impossible de charger ${url} (${res.status})`);
  const txt = await res.text();
  return parseFunctionsWithDocsFromSource(txt);
}

/* -------- Auto-init pour htdocs/htdocs (remplir la section JS) -------- */
document.addEventListener('DOMContentLoaded', async () => {
  const listEl = document.getElementById('js-functions');
  if (!listEl) return; // pas la page hub

  try {
    const catalog = await listJsFunctionsWithDocs('../script/script.js');
    listEl.innerHTML = '';
    if (!catalog.length) {
      listEl.innerHTML = '<li class="todo muted">Aucune fonction détectée.</li>';
      return;
    }
    for (const fn of catalog) {
      const li = document.createElement('li');
      li.className = 'in-progress';
      li.innerHTML =
        `<code>${fn.name}</code> <span class="muted">${fn.sig} — ${fn.kind}</span>` +
        (fn.doc ? `<div class="muted">${fn.doc}</div>` : '');
      listEl.appendChild(li);
    }
  } catch (e) {
    listEl.innerHTML = `<li class="todo muted">Erreur : ${e.message}</li>`;
  }
});
