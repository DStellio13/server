// script/inventaire.js — version debug + API forcée
(function () {
  const API = '/inventaire/api/items.php';

  // DOM
  const $list = document.getElementById('inv-list');
  const $empty = document.getElementById('inv-empty');
  const $search = document.getElementById('inv-search');
  const $filterCat = document.getElementById('inv-filter-category');
  const $sort = document.getElementById('inv-sort');
  const $add = document.getElementById('inv-add');

  // Compteur dans le header (si présent)
  const $count = document.getElementById('inv-count');

  const $drawer = document.getElementById('inv-drawer');
  const $drawerClose = document.getElementById('inv-drawer-close');
  const $backdrop = document.getElementById('inv-drawer-backdrop');
  const $form = document.getElementById('inv-form');
  const $delete = document.getElementById('inv-delete');

  let state = { query:'', category:'', sort:'name.asc', page:1, limit:500, data:[] };

  async function fetchList(){
    const p = new URLSearchParams();
    if (state.query) p.set('q', state.query);
    if (state.category) p.set('category', state.category);
    if (state.sort) p.set('sort', state.sort);
    p.set('page', String(state.page));
    p.set('limit', String(state.limit));

    const url = `${API}?${p.toString()}`;
    console.log('[inventaire] GET', url);

    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Erreur API');
    state.data = json.data || [];
    console.log('[inventaire] rows:', state.data.length);

    if ($count) $count.textContent = String(state.data.length);
    render();
  }

  function render(){
    $list.innerHTML = '';
    if (!state.data.length){ $empty.hidden = false; return; }
    $empty.hidden = true;

    for (const it of state.data){
      const li = document.createElement('li');
      li.className = 'card';

      const left = document.createElement('div');
      const name = document.createElement('div');
      name.className = 'name';
      name.textContent = it.name;

      const meta = document.createElement('div');
      meta.className = 'meta';
      const chips = [];
      if (it.category) chips.push(`cat: ${it.category}`);
      if (it.product_code) chips.push(`code: ${it.product_code}`);
      if (it.version) chips.push(`v: ${it.version}`);
      if (it.location) chips.push(`lieu: ${it.location}`);
      meta.textContent = chips.join(' • ') || '—';

      const actions = document.createElement('div');
      actions.className = 'actions';
      const btnEdit = document.createElement('button');
      btnEdit.className = 'btn';
      btnEdit.textContent = 'Modifier';
      btnEdit.addEventListener('click', () => openDrawer(it));
      actions.appendChild(btnEdit);

      left.appendChild(name);
      left.appendChild(meta);
      li.appendChild(left);
      li.appendChild(actions);
      $list.appendChild(li);
    }
  }

  // Drawer (si tu l'as sur la page)
  function openDrawer(item){ if (!$drawer) return; fillForm(item); $drawer.setAttribute('aria-hidden','false'); }
  function closeDrawer(){ if (!$drawer) return; $drawer.setAttribute('aria-hidden','true'); }
  $drawerClose?.addEventListener('click', closeDrawer);
  $backdrop?.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeDrawer(); });

  function fillForm(it){
    if (!$form) return;
    $form.elements.id.value = it?.id ?? '';
    $form.elements.name.value = it?.name ?? '';
    $form.elements.category.value = it?.category ?? '';
    $form.elements.product_code.value = it?.product_code ?? '';
    $form.elements.version.value = it?.version ?? '';
    $form.elements.location.value = it?.location ?? '';
    $form.elements.acquired_at.value = it?.acquired_at ?? '';
    $form.elements.is_functional.value = (it?.is_functional ?? 1) ? '1' : '0';
    $form.elements.notes.value = it?.notes ?? '';
    $form.elements.photo.value = it?.photo ?? '';
  }

  async function upsertItem(payload){
    const res = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Échec sauvegarde');
  }
  async function deleteItem(id){
    const res = await fetch(API, { method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id}) });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Échec suppression');
  }

  $form?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = Object.fromEntries(new FormData($form).entries());
    payload.id = payload.id ? Number(payload.id) : undefined;
    payload.is_functional = Number(payload.is_functional || 1);
    try { await upsertItem(payload); closeDrawer(); await fetchList(); }
    catch(err){ alert(err.message); }
  });

  $delete?.addEventListener('click', async ()=>{
    const id = Number($form.elements.id.value);
    if (!id) { closeDrawer(); return; }
    if (!confirm('Supprimer cet article ?')) return;
    try { await deleteItem(id); closeDrawer(); await fetchList(); }
    catch(err){ alert(err.message); }
  });

  $add?.addEventListener('click', ()=>{
    if (!$form) return;
    fillForm({ name:'', category:'', product_code:'', version:'', notes:'', acquired_at:'', location:'', is_functional:1, photo:'' });
    openDrawer({});
  });

  $search?.addEventListener('input', async ()=>{ state.query = $search.value; await fetchList(); });
  $filterCat?.addEventListener('change', async ()=>{ state.category = $filterCat.value; await fetchList(); });
  $sort?.addEventListener('change', async ()=>{ state.sort = $sort.value; await fetchList(); });

  // Lancement
  fetchList().catch(err=>{
    console.error('[inventaire] API error:', err);
    $empty.hidden = false;
    $empty.textContent = 'Erreur de chargement : ' + err.message;
  });
})();
