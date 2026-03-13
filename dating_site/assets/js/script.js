// Smooth scroll for dashboard links
document.querySelectorAll('a[href^="#"]').forEach(anchor=>{
    anchor.addEventListener('click', function(e){
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({behavior:'smooth'});
    });
});

// Auto-scroll chat to bottom
const chatBox = document.getElementById('chat-box');
if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

// Simple swipe animation
const cards = document.querySelectorAll('#swipe-container .card');
let index = 0;
function swipe(action){
    if(index >= cards.length) return;
    const card = cards[index];
    card.style.transform = `translateX(${action==='liked'?200:-200}px) rotate(${action==='liked'?15:-15}deg)`;
    card.style.opacity = 0;
    index++;
}
document.getElementById('like-btn')?.addEventListener('click', ()=>swipe('liked'));
document.getElementById('dislike-btn')?.addEventListener('click', ()=>swipe('disliked'));
