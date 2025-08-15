(function () {
  const root = document.getElementById('quickbar');
  if (!root) return;
  const toggle = root.querySelector('.qb-toggle');
  const panel  = document.getElementById('qb-panel');
  const toast  = root.querySelector('.qb-toast');
  const formProject = document.getElementById('qb-form-project');
  const formTask    = document.getElementById('qb-form-task');
  const formItem    = document.getElementById('qb-form-item');

  // Persistance état ouvert/fermé
  const LS_KEY = 'quickbar:open';
  setOpen(localStorage.getItem(LS_KEY) === '1');
  toggle.addEventListener('click', () => setOpen(panel.hidden));
  function setOpen(open){ panel.hidden=!open; toggle.setAttribute('aria-expanded', String(open)); toggle.textContent=open?'×':'＋'; localStorage.setItem(LS_KEY, open?'1':'0'); }

  async function postJSON(payload){
    const res = await fetch('/includes/quickbar_handle.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
    const json = await res.json().catch(()=>({}));
    if (!res.ok || !json.ok) throw new Error(json.data?.error || res.statusText);
    return json.data;
  }
  const ok = (m)=>{ toast.textContent='✅ '+m; toast.hidden=false; clearTimeout(ok._t); ok._t=setTimeout(()=>toast.hidden=true, 2400); };
  const ko = (m)=>{ toast.textContent='⚠️ '+m; toast.style.background='#fff4e5'; toast.style.color='#7a3e00'; toast.style.borderColor='#ffd8a8'; toast.hidden=false; };

  formProject?.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd = new FormData(formProject);
    try { await postJSON({action:'add_project', name:fd.get('name'), slug:fd.get('slug')}); ok('Projet ajouté'); formProject.reset(); }
    catch(err){ ko(err.message); }
  });

  formTask?.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd = new FormData(formTask);
    try {
      await postJSON({
        action:'add_task',
        title:fd.get('title'),
        project_slug:fd.get('project_slug'),
        status:fd.get('status'),
        priority:Number(fd.get('priority')||3)
      });
      ok('Tâche ajoutée'); formTask.reset();
    } catch(err){ ko(err.message); }
  });

  formItem?.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd = new FormData(formItem);
    try { await postJSON({action:'add_item', task_id:Number(fd.get('task_id')), title:fd.get('title')}); ok('Item ajouté'); formItem.reset(); }
    catch(err){ ko(err.message); }
  });
})();
