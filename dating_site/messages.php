<?php
// public/messages.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
include __DIR__ . '/../includes/header.php';

$me = current_user($pdo);
$to = intval($_GET['to'] ?? 0);

// list of conversations (distinct users)
$stmt = $pdo->prepare("SELECT u.id,u.name,u.avatar, m.last_at, m.last_msg FROM (
  SELECT IF(sender_id = ? , receiver_id, sender_id) as other_id,
         MAX(created_at) as last_at,
         SUBSTRING_INDEX(GROUP_CONCAT(body ORDER BY created_at DESC SEPARATOR '||~||'), '||~||', 1) as last_msg
  FROM messages
  WHERE sender_id = ? OR receiver_id = ?
  GROUP BY other_id
) as m JOIN users u ON u.id = m.other_id ORDER BY m.last_at DESC");
$stmt->execute([$me['id'],$me['id'],$me['id']]);
$conv = $stmt->fetchAll();
?>
<div class="card" style="display:flex;gap:12px">
  <div style="width:260px">
    <h3>Conversations</h3>
    <?php foreach($conv as $c): ?>
      <div class="card" style="margin-bottom:8px">
        <a href="/public/messages.php?to=<?php echo $c['id']; ?>">
          <img src="<?php echo e($c['avatar']?:'/public/assets/default-avatar.png'); ?>" style="width:40px;height:40px;border-radius:8px;vertical-align:middle">
          <strong><?php echo e($c['name']); ?></strong>
        </a>
        <div class="small-muted"><?php echo e(substr($c['last_msg'],0,60)); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div style="flex:1">
    <?php if($to): 
      $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
      $stmt->execute([$to]);
      $other = $stmt->fetch();
    ?>
      <h3>Chat with <?php echo e($other['name']); ?></h3>
      <div id="chat" class="chat-box"></div>
      <form id="sendMsg" onsubmit="return false;" style="display:flex;gap:8px;margin-top:8px">
        <input id="msgInput" placeholder="Type a message..." style="flex:1;padding:8px;border-radius:8px;border:1px solid #ddd">
        <button class="btn" id="sendBtn">Send</button>
      </form>
      <script>
        const meId = <?php echo $me['id']; ?>;
        const toId = <?php echo $to; ?>;
        async function fetchMessages(){
          const res = await fetch('/ajax/message_fetch.php?with=' + toId);
          const data = await res.json();
          const el = document.getElementById('chat');
          el.innerHTML = '';
          data.forEach(m=>{
            const div = document.createElement('div');
            div.className = 'message ' + (m.sender_id==meId ? 'me':'');
            div.textContent = m.body + ' • ' + m.created_at;
            el.appendChild(div);
          });
          el.scrollTop = el.scrollHeight;
        }
        document.getElementById('sendBtn').addEventListener('click', async ()=>{
          const body = document.getElementById('msgInput').value.trim();
          if(!body) return;
          await fetch('/ajax/message_send.php', {method:'POST',headers:{'Content-Type':'application/json'},body: JSON.stringify({to:toId, body})});
          document.getElementById('msgInput').value='';
          await fetchMessages();
        });
        fetchMessages();
        setInterval(fetchMessages, 3000);
      </script>
    <?php else: ?>
      <div class="small-muted card">Select a conversation or message someone from a profile.</div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
