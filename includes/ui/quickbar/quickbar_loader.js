// Ajoute une iframe Quickbar sans transformer quickbar en "projet"
(function(){
  if (document.getElementById('qb-embed-frame')) return;
  // Récup slug depuis <meta name="x-project-slug" content="..."> si présent
  const meta = document.querySelector('meta[name="x-project-slug"]');
  const slug = meta ? meta.content : '';
  const f = document.createElement('iframe');
  f.id = 'qb-embed-frame';
  f.src = '/includes/quickbar_embed.php' + (slug ? ('?project='+encodeURIComponent(slug)) : '');
  f.style.position='fixed'; f.style.right='16px'; f.style.bottom='16px'; f.style.width='0'; f.style.height='0';
  f.style.border='0'; f.style.zIndex='9999';
  // L’iframe dimensionnera elle‑même son panneau via CSS interne—le bouton reste visible.
  // Astuce: on laisse width/height à 0 car quickbar.php est positionné fixed lui‑même.
  document.body.appendChild(f);
})();
