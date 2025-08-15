document.addEventListener('DOMContentLoaded', () => {
  const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
  const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
    v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ?
    v1 - v2 : v1.toString().localeCompare(v2)
  )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

  document.querySelectorAll('.candidature-table th').forEach(th => {
    th.addEventListener('click', () => {
      const table = th.closest('table');
      const tbody = table.querySelector('tbody');
      Array.from(tbody.querySelectorAll('tr'))
        .sort(comparer(Array.from(th.parentNode.children).indexOf(th), th.asc = !th.asc))
        .forEach(tr => tbody.appendChild(tr));
    });
  });
});
  document.querySelectorAll('.statut-select').forEach(select => {
    select.addEventListener('change', function () {
      const id = this.dataset.id;
      const statut = this.value;

      fetch('update_statut.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}&statut=${encodeURIComponent(statut)}`
      })
      .then(response => response.text())
      .then(data => {
        if (data !== "OK") {
          alert("Erreur lors de la mise à jour.");
        }
      })
      .catch(error => {
        alert("Erreur réseau.");
        console.error(error);
      });
    });
  });