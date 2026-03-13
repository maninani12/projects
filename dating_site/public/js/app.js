// public/js/app.js
async function postJSON(url, data) {
  const res = await fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data)
  });
  return res.json();
}

// like button handler
document.addEventListener('click', async (e) => {
  if (e.target.matches('[data-like]')) {
    const targetId = e.target.dataset.like;
    const resp = await postJSON('/ajax/like.php', {target_id: targetId});
    if (resp.success) e.target.textContent = resp.status === 'matched' ? 'Matched' : 'Liked';
  }
});
