document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');

    toggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
    });

    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
});
document.addEventListener('DOMContentLoaded', () => {

    // Comment submission
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const postId = form.dataset.postId;
            const content = form.comment_content.value;

            fetch('../public/actions.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=add_comment&post_id=${postId}&content=${encodeURIComponent(content)}`
            })
            .then(res => res.text())
            .then(data => {
                if(data === 'success') location.reload();
            });
        });
    });

    // Like button
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const postId = btn.dataset.postId;

            fetch('../public/actions.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `action=toggle_like&post_id=${postId}`
            })
            .then(res => res.text())
            .then(count => {
                btn.textContent = `Like (${count})`;
            });
        });
    });

});
// Chat system
const chatForm = document.getElementById('chat-form');
if(chatForm){
    const chatBox = document.getElementById('chat-box');
    const friendId = chatForm.dataset.friendId;

    // Send message
    chatForm.addEventListener('submit', e => {
        e.preventDefault();
        const message = chatForm.message.value;

        fetch('../public/actions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=send_message&receiver_id=${friendId}&content=${encodeURIComponent(message)}`
        }).then(res => res.text()).then(data => {
            if(data==='success'){
                chatForm.message.value = '';
                fetchMessages();
            }
        });
    });

    // Poll messages every 2 seconds
    function fetchMessages(){
        fetch('../public/actions.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`action=fetch_messages&friend_id=${friendId}`
        })
        .then(res=>res.json())
        .then(data=>{
            chatBox.innerHTML = '';
            data.forEach(msg=>{
                const div = document.createElement('div');
                div.className = msg.sender_id==<?= $user_id ?> ? 'my-msg' : 'friend-msg';
                div.innerHTML = `<p>${msg.content}</p><span>${msg.created_at}</span>`;
                chatBox.appendChild(div);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    fetchMessages();
    setInterval(fetchMessages,2000);
}
// Real-time notifications
const notifBox = document.querySelector('.notif-dropdown');
function fetchNotifications(){
    fetch('../public/actions.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=fetch_notifications'
    })
    .then(res=>res.json())
    .then(data=>{
        if(notifBox){
            notifBox.innerHTML = '';
            data.forEach(n=>{
                const li = document.createElement('li');
                li.innerHTML = `${n.message} <span class="notif-date">${n.created_at}</span>`;
                notifBox.appendChild(li);
            });
        }
    });
}

setInterval(fetchNotifications, 5000);
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("theme-toggle");
    const body = document.body;

    // Load saved theme
    if(localStorage.getItem("theme") === "dark"){
        body.classList.remove("light-mode");
        body.classList.add("dark-mode");
    } else {
        body.classList.add("light-mode");
    }

    toggleBtn.addEventListener("click", () => {
        body.classList.toggle("dark-mode");
        body.classList.toggle("light-mode");

        // Save preference
        if(body.classList.contains("dark-mode")){
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    });
});
